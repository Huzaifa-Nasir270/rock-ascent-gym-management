<?php
require_once('config/functions.php');

$weightGainExercises = [
    [
        'name' => 'Bench Press',
        'description' => 'A compound exercise that works the pectoralis major as well as the triceps and deltoids.',
        'media_path' => 'assets/images/Weight Gain/bench press.png',
        'difficulty' => 'Intermediate'
    ],
    [
        'name' => 'Chest Fly',
        'description' => 'An isolation exercise that targets the chest muscles by moving the arms in a wide arc.',
        'media_path' => 'assets/images/Weight Gain/Chest Fly.png',
        'difficulty' => 'Beginner'
    ],
    [
        'name' => 'Tricep Pushdown',
        'description' => 'A strength training exercise used for strengthening the triceps brachii muscle.',
        'media_path' => 'assets/images/Weight Gain/Tricep Pushdown.png',
        'difficulty' => 'Beginner'
    ],
    [
        'name' => 'Bicep Curl',
        'description' => 'A standard exercise for building the size and strength of the biceps.',
        'media_path' => 'assets/images/Weight Gain/Bicep Curl.png',
        'difficulty' => 'Beginner'
    ],
    [
        'name' => 'Hammer Curl',
        'description' => 'A variation of the bicep curl that also targets the brachialis and brachioradialis.',
        'media_path' => 'assets/images/Weight Gain/Hammer Curl.png',
        'difficulty' => 'Beginner'
    ],
    [
        'name' => 'Lat Pulldown',
        'description' => 'A compound exercise designed to develop the latissimus dorsi muscle.',
        'media_path' => 'assets/images/Weight Gain/Lat Pulldown.png',
        'difficulty' => 'Beginner'
    ],
    [
        'name' => 'Seated Row',
        'description' => 'An exercise that targets the muscles in your back and forearms.',
        'media_path' => 'assets/images/Weight Gain/Seated Row.png',
        'difficulty' => 'Intermediate'
    ]
];

foreach ($weightGainExercises as $ex) {
    $name = $ex['name'];
    $desc = $ex['description'];
    $path = $ex['media_path'];
    $diff = $ex['difficulty'];
    
    // Check if exists
    $stmt = $conn->prepare("SELECT exercise_id FROM exercises WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO exercises (name, description, media_path, difficulty_level, instructor_id) VALUES (?, ?, ?, ?, NULL)");
        $stmt->bind_param("ssss", $name, $desc, $path, $diff);
        $stmt->execute();
        echo "Inserted: $name\n";
    } else {
        echo "Skipped (already exists): $name\n";
    }
}
echo "Done seeding weight gain exercises.\n";
