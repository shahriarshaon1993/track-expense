<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Home Rant', 'description' => 'Spending on unplanned home repairs, maintenance, or small household frustrations', 'is_default' => true],
            ['name' => 'Food & Drink', 'description' => 'Expenses for food and beverages', 'is_default' => true],
            ['name' => 'Groceries', 'description' => 'Shopping for daily groceries and household essentials', 'is_default' => true],
            ['name' => 'Dining Out', 'description' => 'Meals and drinks outside the home', 'is_default' => true],
            ['name' => 'Transportation', 'description' => 'Daily transportation costs such as fuel, public transit, rideshare', 'is_default' => true],
            ['name' => 'Car & Vehicle', 'description' => 'Car payments, maintenance, insurance, and related expenses', 'is_default' => true],
            ['name' => 'Shopping', 'description' => 'Personal or household shopping expenses', 'is_default' => true],
            ['name' => 'Clothing & Accessories', 'description' => 'Clothes, shoes, and personal accessories', 'is_default' => true],
            ['name' => 'Entertainment', 'description' => 'Expenses for recreation, movies, events, and hobbies', 'is_default' => true],
            ['name' => 'Health', 'description' => 'Healthcare, medical visits, medications, and wellness', 'is_default' => true],
            ['name' => 'Fitness & Sports', 'description' => 'Gym memberships, sports, and fitness activities', 'is_default' => true],
            ['name' => 'Education', 'description' => 'Costs for education, courses, training, and books', 'is_default' => true],
            ['name' => 'Bills & Utilities', 'description' => 'Payments for electricity, water, internet, phone, and other utilities', 'is_default' => true],
            ['name' => 'Insurance', 'description' => 'Health, life, car, or other insurance premiums', 'is_default' => true],
            ['name' => 'Investment', 'description' => 'Funds allocated for investments', 'is_default' => true],
            ['name' => 'Savings', 'description' => 'Funds set aside for savings or emergency funds', 'is_default' => true],
            ['name' => 'Gifts & Donations', 'description' => 'Gifts, charity, and donations to others', 'is_default' => true],
            ['name' => 'Travel & Vacation', 'description' => 'Travel expenses, accommodation, flights, and vacations', 'is_default' => true],
            ['name' => 'Subscriptions', 'description' => 'Monthly or annual subscriptions such as streaming, apps, or software', 'is_default' => true],
            ['name' => 'Home & Furniture', 'description' => 'Furniture, home improvement, and appliances', 'is_default' => true],
            ['name' => 'Pets', 'description' => 'Pet food, care, vet visits, and accessories', 'is_default' => true],
            ['name' => 'Other', 'description' => 'Other categories not listed above', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
