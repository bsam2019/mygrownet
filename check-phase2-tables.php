<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'investor_risk_assessments',
    'investor_scenario_projections', 
    'investor_exit_opportunities',
    'investor_questions',
    'investor_question_answers',
    'investor_feedback',
    'investor_surveys',
    'investor_survey_responses',
    'investor_polls',
    'investor_poll_votes',
    'company_valuations',
    'shareholder_resolutions',
    'shareholder_votes',
    'proxy_delegations',
    'risk_assessments',
    'scenario_models',
    'exit_projections',
    'investor_question_upvotes',
    'share_transfer_requests',
    'liquidity_events',
    'liquidity_event_participations',
    'shareholder_forum_categories',
    'shareholder_forum_topics',
    'shareholder_forum_replies',
    'shareholder_directory_profiles',
    'shareholder_contact_requests',
];

foreach ($tables as $table) {
    $status = Schema::hasTable($table) ? 'EXISTS' : 'MISSING';
    echo "$table: $status\n";
}
