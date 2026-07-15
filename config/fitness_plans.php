<?php
/**
 * Fitness Plans Data Store
 * Centrally managed diet and workout plans for various fitness goals.
 */

if (!function_exists('getFitnessPlans')) {
    function getFitnessPlans() {
        return [
            'Weight Gain' => [
                'color' => '#f59e0b',
                'focus' => 'High calories and muscle growth.',
                'image' => 'Weight Gain.png',
                'diet' => [
                    ['icon' => 'egg', 'title' => 'Breakfast', 'desc' => 'Eggs, milk, banana, brown bread'],
                    ['icon' => 'bread-slice', 'title' => 'Mid-morning', 'desc' => 'Peanut butter sandwich, nuts'],
                    ['icon' => 'drumstick-bite', 'title' => 'Lunch', 'desc' => 'Chicken/beef, rice/roti, yogurt'],
                    ['icon' => 'blender', 'title' => 'Evening snack', 'desc' => 'Banana shake or protein shake'],
                    ['icon' => 'fish', 'title' => 'Dinner', 'desc' => 'Chicken/fish, vegetables, rice/roti'],
                    ['icon' => 'glass-whiskey', 'title' => 'Before sleep', 'desc' => 'Milk']
                ],
                'workout' => [
                    ['name' => 'Bench Press', 'desc' => 'Builds chest strength and muscle mass.', 'img' => 'bench press.png'],
                    ['name' => 'Chest Fly', 'desc' => 'Isolates chest muscles for better definition.', 'img' => 'Chest Fly.png'],
                    ['name' => 'Tricep Pushdown', 'desc' => 'Targets the triceps for arm thickness.', 'img' => 'Tricep Pushdown.png'],
                    ['name' => 'Bicep Curl', 'desc' => 'Classic exercise for building bicep peaks.', 'img' => 'Bicep Curl.png'],
                    ['name' => 'Hammer Curl', 'desc' => 'Develops forearm and bicep thickness.', 'img' => 'Hammer Curl.png'],
                    ['name' => 'Lat Pulldown', 'desc' => 'Develops back width and V-taper.', 'img' => 'Lat Pulldown.png'],
                    ['name' => 'Seated Row', 'desc' => 'Improves back thickness and posture.', 'img' => 'Seated Row.png']
                ]
            ],
            'Weight Loss' => [
                'color' => '#3b82f6',
                'focus' => 'Low calories and fat loss.',
                'image' => 'Weight Lose.png',
                'diet' => [
                    ['icon' => 'bowl-rice', 'title' => 'Breakfast', 'desc' => 'Oats, 2 boiled eggs, green tea'],
                    ['icon' => 'apple-whole', 'title' => 'Mid-morning', 'desc' => 'Apple or orange'],
                    ['icon' => 'fish', 'title' => 'Lunch', 'desc' => 'Grilled chicken/fish, salad, 1 roti'],
                    ['icon' => 'mug-hot', 'title' => 'Evening snack', 'desc' => 'Green tea, almonds'],
                    ['icon' => 'leaf', 'title' => 'Dinner', 'desc' => 'Boiled vegetables, small portion chicken'],
                    ['icon' => 'glass-water', 'title' => 'Before sleep', 'desc' => 'Warm water or green tea']
                ],
                'workout' => [
                    ['name' => 'Bodyweight Squats', 'desc' => 'Burns calories while toning lower body.', 'img' => 'Bodyweight Squats.png'],
                    ['name' => 'Planks', 'desc' => 'Strengthens core and improves stability.', 'img' => 'Planks.png'],
                    ['name' => 'Push Ups', 'desc' => 'Tones upper body and core.', 'img' => 'Push Ups.png'],
                    ['name' => 'Tricep Dips', 'desc' => 'Bodyweight exercise for tricep toning.', 'img' => 'Tricep Dips.png']
                ]
            ],
            'Stay Fit' => [
                'color' => '#10b981',
                'focus' => 'Balanced nutrition and maintenance.',
                'image' => 'Stay Fit.png',
                'diet' => [
                    ['icon' => 'egg', 'title' => 'Breakfast', 'desc' => 'Eggs, brown bread, milk'],
                    ['icon' => 'fruit-apple', 'title' => 'Mid-morning', 'desc' => 'Fruits'],
                    ['icon' => 'bowl-food', 'title' => 'Lunch', 'desc' => 'Chicken/daal, roti, vegetables'],
                    ['icon' => 'ice-cream', 'title' => 'Evening snack', 'desc' => 'Yogurt or smoothie'],
                    ['icon' => 'leaf', 'title' => 'Dinner', 'desc' => 'Light meal (vegetables + protein)'],
                    ['icon' => 'glass-whiskey', 'title' => 'Before sleep', 'desc' => 'Milk']
                ],
                'workout' => [
                    ['name' => 'Deadlift', 'desc' => 'Compound movement for full body strength.', 'img' => 'Deadlift.png'],
                    ['name' => 'Pull Ups', 'desc' => 'Superior upper body pull exercise.', 'img' => 'Pull Ups.png'],
                    ['name' => 'Shoulder Press', 'desc' => 'Builds powerful and stable shoulders.', 'img' => 'Shoulder Press.png'],
                    ['name' => 'Tricep Pushdown', 'desc' => 'Isolates triceps for arm definition.', 'img' => 'Tricep Pushdown.png']
                ]
            ]
        ];
    }
}
