<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use JfBiswajit\PHPBigQuery\Facades\BigQuery;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Http;

Route::get('/clients', function () {
    $bigQuery = new BigQueryClient([
        'projectId' => config('bigquery.projectId'),
        'keyFile' => json_decode(config('bigquery.keyFile'), true),
    ]);

    $queryJobConfig = $bigQuery->query('SELECT * FROM maxima_hackathon.patientdata');
    $clients = $bigQuery->runQuery($queryJobConfig);
    return collect($clients)->toArray();
});

Route::post('/get-suggestions', function () {
    $questions = request()->questions;
    $answers = request()->answers;

    // dd($questions, $answers);

    // Merge answers and questions
    $answersAndQuestions = collect($questions)->mapWithKeys(function ($question, $index) use ($answers) {
        // return [
        //     'question' => $question,
        //     'answers' => $answers[$index],
        // ];
        return [
            $question => $answers[$index],
        ];
    });

    // Fetch patient data from BQ
    $bigQuery = new BigQueryClient([
        'projectId' => config('bigquery.projectId'),
        'keyFile' => json_decode(config('bigquery.keyFile'), true),
    ]);

    $queryJobConfig = $bigQuery->query("SELECT * FROM maxima_hackathon.patientdata where id = ".htmlspecialchars($answers[2])." limit 1");
    $clients = $bigQuery->runQuery($queryJobConfig);
    $patient = collect($clients)->toArray();

    // Process patient data
    $patientData = $patient[0];
    $patientData['date_of_birth'] = $patientData['date_of_birth']->formatAsString();

    // Make Gemini API call
    $response = Http::post('https://app-v5oh3jgiya-ez.a.run.app/test', [
        'response' => [
            'answersAndQuestions' => $answersAndQuestions,
            'patientData' => $patientData,
        ],
    ]);

    // Return Gemini suggestions
    return $response->json();
});

Route::get('/', function () {
    $bigQuery = new BigQueryClient([
        'projectId' => config('bigquery.projectId'),
        'keyFile' => json_decode(config('bigquery.keyFile'), true),
    ]);

    $queryJobConfig = $bigQuery->query('SELECT * FROM maxima_hackathon.patientdata');
    $clients = collect($bigQuery->runQuery($queryJobConfig))->toArray();
    $clients = collect($clients)->toArray();

    return view('app', ['clients' => $clients]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
