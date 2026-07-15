<?php
/**
 * Recommendation Engine for Gym Management System
 */

class RecommendationEngine {
    
    public static function getDietPlan($goal, $weight = 70) {
        $plans = [
            'Weight Gain' => [
                'breakfast' => ['items' => 'Oatmeal with whole milk, peanut butter, and banana', 'calories' => 800],
                'lunch' => ['items' => 'Grilled chicken breast with 2 cups of brown rice and avocado', 'calories' => 900],
                'dinner' => ['items' => 'Salmon fillet with sweet potato and steamed broccoli', 'calories' => 850],
                'snacks' => ['items' => 'Greek yogurt with nuts and honey', 'calories' => 450],
                'total_calories' => 3000
            ],
            'Weight Loss' => [
                'breakfast' => ['items' => 'Egg white omelet with spinach and tomatoes', 'calories' => 350],
                'lunch' => ['items' => 'Tuna salad with mixed greens and lemon vinaigrette', 'calories' => 450],
                'dinner' => ['items' => 'Baked cod with cauliflower rice and asparagus', 'calories' => 400],
                'snacks' => ['items' => 'Apple slices or a handful of almonds', 'calories' => 200],
                'total_calories' => 1400
            ],
            'Stay Fit' => [
                'breakfast' => ['items' => 'Scrambled eggs on whole grain toast with avocado', 'calories' => 550],
                'lunch' => ['items' => 'Quinoa bowl with chickpeas, kale, and roasted veggies', 'calories' => 650],
                'dinner' => ['items' => 'Lean beef stir-fry with mixed vegetables and small portion of rice', 'calories' => 700],
                'snacks' => ['items' => 'Protein shake or low-fat cottage cheese', 'calories' => 300],
                'total_calories' => 2200
            ]
        ];

        return $plans[$goal] ?? $plans['Stay Fit'];
    }

    public static function getWorkoutPlan($goal) {
        $plans = [
            'Weight Gain' => [
                'exercises' => [
                    [
                        'name' => 'Bench Press',
                        'sets_reps' => '4 Sets x 8-10 Reps',
                        'description' => 'Target: Chest. Lay on bench, push bar up from mid-chest.',
                        'image' => '../assets/images/exercises/bench_press.jpg'
                    ],
                    [
                        'name' => 'Shoulder Press',
                        'sets_reps' => '3 Sets x 10-12 Reps',
                        'description' => 'Target: Shoulders. Push dumbbells or bar overhead while seated or standing.',
                        'image' => '../assets/images/exercises/shoulder_press.jpg'
                    ],
                    [
                        'name' => 'Bicep Curl',
                        'sets_reps' => '3 Sets x 12-15 Reps',
                        'description' => 'Target: Biceps. Curl dumbbells towards shoulders while keeping elbows stationary.',
                        'image' => '../assets/images/exercises/bicep_curl.jpg'
                    ],
                    [
                        'name' => 'Tricep Pushdown',
                        'sets_reps' => '3 Sets x 12-15 Reps',
                        'description' => 'Target: Triceps. Use cable machine to push bar down until arms are straight.',
                        'image' => '../assets/images/exercises/tricep_pushdown.jpg'
                    ]
                ]
            ],
            'Weight Loss' => [
                'exercises' => [
                    [
                        'name' => 'Running / Jogging',
                        'sets_reps' => '30 - 45 Minutes',
                        'description' => 'Target: Cardiovascular Health. Maintain a steady pace on treadmill or outdoors.',
                        'image' => '../assets/images/exercises/running.jpg'
                    ],
                    [
                        'name' => 'Push-ups',
                        'sets_reps' => '3 Sets x Max Reps',
                        'description' => 'Target: Upper Body. Keep core tight and lower chest to floor.',
                        'image' => '../assets/images/exercises/pushups.jpg'
                    ],
                    [
                        'name' => 'Squats',
                        'sets_reps' => '4 Sets x 15-20 Reps',
                        'description' => 'Target: Legs. Lower hips as if sitting in a chair, keep back straight.',
                        'image' => '../assets/images/exercises/squats.jpg'
                    ],
                    [
                        'name' => 'Plank',
                        'sets_reps' => '3 Sets x 60 Seconds',
                        'description' => 'Target: Core. Hold a push-up position on forearms, keeping body flat.',
                        'image' => '../assets/images/exercises/plank.jpg'
                    ]
                ]
            ],
            'Stay Fit' => [
                'exercises' => [
                    [
                        'name' => 'Pull-ups',
                        'sets_reps' => '3 Sets x 8-12 Reps',
                        'description' => 'Target: Back. Pull your body up until chin clears the bar.',
                        'image' => '../assets/images/exercises/pullups.jpg'
                    ],
                    [
                        'name' => 'Lunges',
                        'sets_reps' => '3 Sets x 12 Reps (per leg)',
                        'description' => 'Target: Legs. Step forward and lower hips until both knees are bent at 90 degrees.',
                        'image' => '../assets/images/exercises/lunges.jpg'
                    ],
                    [
                        'name' => 'Dumbbell Rows',
                        'sets_reps' => '3 Sets x 12 Reps',
                        'description' => 'Target: Back. Pull weight towards hip while leaning forward with one arm supported.',
                        'image' => '../assets/images/exercises/db_rows.jpg'
                    ],
                    [
                        'name' => 'Bicycle Crunches',
                        'sets_reps' => '3 Sets x 20 Reps',
                        'description' => 'Target: Abs. Bring opposite elbow to opposite knee in a cycling motion.',
                        'image' => '../assets/images/exercises/bicycle_crunches.jpg'
                    ]
                ]
            ]
        ];

        return $plans[$goal] ?? $plans['Stay Fit'];
    }
}
