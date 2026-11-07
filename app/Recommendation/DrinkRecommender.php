<?php

declare(strict_types=1);

namespace App\Recommendation;

use App\Models\Drink;
use App\Models\Ingredient;
use App\Taxonomy\TaggingRules;
use Illuminate\Support\Collection;

/** Nước uống kèm bữa: kho riêng, không thay món ăn hoặc tính thêm kcal. */
final class DrinkRecommender
{
    private const HOME_GROUPS = ['giai-khat', 'nuoc-ep', 'sinh-to'];

    private const EXCLUDED_GROUPS = ['cocktail', 'cocktail-co-con', 'mocktail', 'bia', 'ruou', 'vang'];

    /** @param array{allergens?: list<string>, vegetarian?: bool} $input */
    public function pick(array $input, string $mode, string $seed): ?Drink
    {
        $query = Drink::query()->with('media')->where('alcohol', false)
            ->where('needs_review', false)
            ->whereIn('kind', ['pha-che', 'dong-goi'])
            ->whereNotIn('drink_group', self::EXCLUDED_GROUPS);

        if ($mode === 'home') {
            $query->where('kind', 'pha-che')->whereIn('drink_group', self::HOME_GROUPS)
                ->where('difficulty', 'dễ')->whereBetween('prep_minutes', [1, 20]);
        }

        $excluded = $input['allergens'] ?? [];
        $vegetarian = (bool) ($input['vegetarian'] ?? false);
        // Nạp một lần, kể cả thành phần con, không truy vấn từng ly/từng dòng.
        $ingredients = ($excluded !== [] || $vegetarian)
            ? Ingredient::with('components:id,slug,category')->get(['id', 'slug', 'category'])->keyBy('slug')
            : collect();
        $excludedGroups = [];
        foreach ($excluded as $slug) {
            $excludedGroups = array_merge($excludedGroups, TaggingRules::allergensFor($slug, $ingredients->get($slug)?->category ?? ''));
        }

        return $query->get()->filter(function (Drink $drink) use ($mode, $excluded, $excludedGroups, $vegetarian, $ingredients) {
            if ((float) $drink->alcohol_pct_estimate > 0
                || (float) ($drink->alcohol_pct_range['min'] ?? 0) > 0
                || (float) ($drink->alcohol_pct_range['max'] ?? 0) > 0) {
                return false;
            }
            if ($mode === 'home') {
                if (empty($drink->recipe_steps) || empty($drink->ingredients)) {
                    return false;
                }
                // Nước mía cần máy ép chuyên dụng; không suy "dễ" thành pha tại nhà.
                if ($drink->drink_group === 'giai-khat' && $drink->technique === 'ép') {
                    return false;
                }
            }
            if ($excluded === [] && ! $vegetarian) {
                return true;
            }
            // Đóng gói/thành phần trang trí chưa ánh xạ: không đoán an toàn hoặc chay.
            if ($drink->kind !== 'pha-che' || empty($drink->ingredients) || ! empty($drink->garnish)) {
                return false;
            }
            foreach ($drink->ingredients as $line) {
                $slug = $line['ingredient_slug'] ?? null;
                if (! is_string($slug) || ! $this->ingredientAllowed($slug, $excluded, $excludedGroups, $vegetarian, $ingredients)) {
                    return false;
                }
            }

            return true;
        })->sortBy(fn (Drink $drink) => hash('sha256', $seed.'|drink|'.$drink->slug))->first();
    }

    private function ingredientAllowed(string $slug, array $excluded, array $excludedGroups, bool $vegetarian, Collection $ingredients, array $visited = []): bool
    {
        $ingredient = $ingredients->get($slug);
        if ($ingredient === null || in_array($slug, $visited, true) || in_array($slug, $excluded, true)) {
            return false;
        }
        if (array_intersect($excludedGroups, TaggingRules::allergensFor($slug, $ingredient->category)) !== []) {
            return false;
        }
        if ($vegetarian && (TaggingRules::originFor($slug, $ingredient->category) === 'dong-vat'
            || isset(TaggingRules::COMPOSITE_ANIMAL[$slug]))) {
            return false;
        }
        foreach ($ingredient->components as $component) {
            if (! $this->ingredientAllowed($component->slug, $excluded, $excludedGroups, $vegetarian, $ingredients, [...$visited, $slug])) {
                return false;
            }
        }

        return true;
    }
}
