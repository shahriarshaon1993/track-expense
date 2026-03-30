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
            // INCOME CATEGORIES
            ['name' => 'Salary', 'description' => 'Monthly or yearly salary from your employer', 'is_default' => true],
            ['name' => 'Freelance / Side Hustle', 'description' => 'Income from freelancing, gigs, or side jobs', 'is_default' => true],
            ['name' => 'Business Income', 'description' => 'Earnings from your own business or entrepreneurial activities', 'is_default' => true],
            ['name' => 'Investments', 'description' => 'Dividends, interest, capital gains, or other investment income', 'is_default' => true],
            ['name' => 'Rental Income', 'description' => 'Income from renting property', 'is_default' => true],
            ['name' => 'Gifts & Windfalls', 'description' => 'Cash gifts, inheritances, or unexpected windfalls', 'is_default' => true],
            ['name' => 'Other Income', 'description' => 'Other sources of income not listed above', 'is_default' => true],

            // Home & Living
            ['name' => 'Home Rant', 'description' => 'Spending on unplanned home repairs, maintenance, or small household frustrations', 'is_default' => true],
            ['name' => 'Home & Furniture', 'description' => 'Furniture, home improvement, and appliances', 'is_default' => true],
            ['name' => 'Utilities', 'description' => 'Payments for electricity, water, internet, phone, and other utilities', 'is_default' => true],
            ['name' => 'Rent / Mortgage', 'description' => 'Monthly rent or mortgage payments', 'is_default' => true],
            ['name' => 'Property Tax', 'description' => 'Taxes related to property ownership', 'is_default' => true],

            // Food & Dining
            ['name' => 'Groceries', 'description' => 'Shopping for daily groceries and household essentials', 'is_default' => true],
            ['name' => 'Dining Out', 'description' => 'Meals and drinks outside the home', 'is_default' => true],
            ['name' => 'Food & Drink', 'description' => 'Expenses for food and beverages', 'is_default' => true],
            ['name' => 'Coffee & Snacks', 'description' => 'Small purchases like coffee, snacks, or quick bites', 'is_default' => true],

            // Transportation
            ['name' => 'Transportation', 'description' => 'Daily transportation costs such as fuel, public transit, rideshare', 'is_default' => true],
            ['name' => 'Car & Vehicle', 'description' => 'Car payments, maintenance, insurance, and related expenses', 'is_default' => true],
            ['name' => 'Fuel', 'description' => 'Gasoline, diesel, or electric charging costs', 'is_default' => true],
            ['name' => 'Public Transit', 'description' => 'Bus, train, metro, or other public transportation', 'is_default' => true],
            ['name' => 'Taxi / Rideshare', 'description' => 'Uber, Lyft, or taxi rides', 'is_default' => true],
            ['name' => 'Parking & Tolls', 'description' => 'Parking fees, tolls, and related costs', 'is_default' => true],

            // Shopping & Personal
            ['name' => 'Shopping', 'description' => 'Personal or household shopping expenses', 'is_default' => true],
            ['name' => 'Clothing & Accessories', 'description' => 'Clothes, shoes, and personal accessories', 'is_default' => true],
            ['name' => 'Electronics', 'description' => 'Gadgets, phones, computers, and accessories', 'is_default' => true],
            ['name' => 'Personal Care', 'description' => 'Cosmetics, haircuts, skincare, and grooming', 'is_default' => true],

            // Health & Fitness
            ['name' => 'Health', 'description' => 'Healthcare, medical visits, medications, and wellness', 'is_default' => true],
            ['name' => 'Fitness & Sports', 'description' => 'Gym memberships, sports, and fitness activities', 'is_default' => true],
            ['name' => 'Medical Insurance', 'description' => 'Health insurance premiums', 'is_default' => true],

            // Education
            ['name' => 'Education', 'description' => 'Costs for education, courses, training, and books', 'is_default' => true],
            ['name' => 'Books & Stationery', 'description' => 'Books, notebooks, and stationery', 'is_default' => true],

            // Finance
            ['name' => 'Investment', 'description' => 'Funds allocated for investments', 'is_default' => true],
            ['name' => 'Savings', 'description' => 'Funds set aside for savings or emergency funds', 'is_default' => true],
            ['name' => 'Insurance', 'description' => 'Life, car, or other insurance premiums', 'is_default' => true],
            ['name' => 'Taxes', 'description' => 'Income tax, property tax, and other taxes', 'is_default' => true],

            // Leisure & Entertainment
            ['name' => 'Entertainment', 'description' => 'Expenses for recreation, movies, events, and hobbies', 'is_default' => true],
            ['name' => 'Hobbies', 'description' => 'Supplies, classes, or activities for hobbies', 'is_default' => true],
            ['name' => 'Subscriptions', 'description' => 'Monthly or annual subscriptions such as streaming, apps, or software', 'is_default' => true],
            ['name' => 'Travel & Vacation', 'description' => 'Travel expenses, accommodation, flights, and vacations', 'is_default' => true],

            // Social & Charity
            ['name' => 'Gifts & Donations', 'description' => 'Gifts, charity, and donations to others', 'is_default' => true],
            ['name' => 'Events & Parties', 'description' => 'Expenses for social gatherings, parties, or celebrations', 'is_default' => true],

            // Pets
            ['name' => 'Pets', 'description' => 'Pet food, care, vet visits, and accessories', 'is_default' => true],

            // Miscellaneous
            ['name' => 'Other', 'description' => 'Other categories not listed above', 'is_default' => true],

            ['name' => 'Transfer', 'description' => 'Cash withdrawn from bank and added to cash wallet for future expenses', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
