<?php
header('Content-Type: application/json');
require_once('../config/functions.php');
requireInstructor();

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please provide an inquiry.']);
    exit;
}

$lowerMsg = strtolower($userMessage);
$reply = "";

// Professional Skill-Improving Responses for Instructors
if (strpos($lowerMsg, 'hypertrophy') !== false) {
    $reply = "For optimal hypertrophy, science suggests a volume of 10-20 sets per muscle group per week, with an intensity of 60-85% 1RM. Emphasize the eccentric phase and ensure members are achieving progressive overload. Metabolic stress and mechanical tension are the two primary drivers you should monitor.";
} elseif (strpos($lowerMsg, 'injury') !== false || strpos($lowerMsg, 'pain') !== false) {
    $reply = "When a member reports pain, first identify if it is acute or chronic. For joint discomfort, check form for mechanical inefficiencies. Recommend the R.I.C.E method for minor strains, but always defer to a physical therapist for persistent pain. Implementing mobility work (dynamic stretching) before sessions is a proven preventive measure.";
} elseif (strpos($lowerMsg, 'periodization') !== false) {
    $reply = "Linear periodization is effective for beginners, but for advanced members, consider Undulating Periodization (varying intensity/volume daily or weekly). This prevents plateaus by constantly challenging the neuromuscular system in different ways. Plan your phases: Macrocycle (year), Mesocycle (month), and Microcycle (week).";
} elseif (strpos($lowerMsg, 'supplements') !== false || strpos($lowerMsg, 'creatine') !== false) {
    $reply = "Creatine Monohydrate is the most researched ergogenic aid. Recommend 3-5g daily to saturate muscle phosphocreatine stores. For performance, caffeine (3-6mg/kg) taken 60 mins pre-workout is highly effective. Advise members that supplements are the 'cherry on top'—80% of results come from consistent training and whole-food nutrition.";
} elseif (strpos($lowerMsg, 'metabolism') !== false || strpos($lowerMsg, 'fat loss') !== false) {
    $reply = "Instruct members that 'starvation mode' is a myth, but metabolic adaptation (adaptive thermogenesis) is real. To counteract this during fat loss, implement 'refeed days' or 'diet breaks' every 4-6 weeks to maintain hormonal balance (Leptin levels). NEAT (Non-Exercise Activity Thermogenesis) often drops during deficits; encourage step counts.";
} elseif (strpos($lowerMsg, 'recovery') !== false || strpos($lowerMsg, 'sleep') !== false) {
    $reply = "Muscle growth happens during sleep, not in the gym. Ensure your members are getting 7-9 hours of quality sleep. High cortisol from overtraining can inhibit MPS (Muscle Protein Synthesis). Monitor for signs of Overtraining Syndrome: persistent fatigue, decreased performance, and sleep disturbances.";
} elseif (strpos($lowerMsg, 'hello') !== false || strpos($lowerMsg, 'hi') !== false) {
    $reply = "Greetings, Coach. I am your Master Advisor. I can provide scientific data on physiology, biomechanics, and sports nutrition to help you optimize your members' performance. What's your inquiry?";
} else {
    $reply = "As an instructor, focusing on the fundamentals of Exercise Physiology and Biomechanics will always yield the best results. Whether it's perfecting a member's squat path or calculating their TDEE precisely, evidence-based coaching is your greatest tool. Could you clarify your question for a more scientific breakdown?";
}

// Professional delay for 'research analysis' feel
usleep(1200000); 

echo json_encode(['reply' => $reply]);
