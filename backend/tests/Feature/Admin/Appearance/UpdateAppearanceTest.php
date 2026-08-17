<?php

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('appearance update follows shared authorization and boundary data', function (array $case) {
    if ($case['actor'] === 'admin') {
        actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        actingAs(regularUser());
    }

    if (in_array('existing_logo', $case['preconditions'], true)) {
        Setting::query()->create([
            'key' => 'appearance.logo_light',
            'value' => 'settings/existing-logo.png',
        ]);
    }

    $request = $case['request'];
    $body = $request['body'];
    $isUploadCase = in_array('png_upload', $case['preconditions'], true)
        || in_array('text_upload', $case['preconditions'], true);

    if ($isUploadCase) {
        $sizeInKilobytes = strlen($body['logo_light']);
        $isTextUpload = in_array('text_upload', $case['preconditions'], true);
        $body['logo_light'] = UploadedFile::fake()->create(
            $isTextUpload ? 'logo.txt' : 'logo.png',
            $sizeInKilobytes,
            $isTextUpload ? 'text/plain' : 'image/png',
        );
    }

    $response = $isUploadCase
        ? $this->post($request['endpoint'], $body, $request['headers'])
        : $this->postJson($request['endpoint'], $body, $request['headers']);

    $storedPath = $response->json('data.logos.logo_light.path');

    try {
        TestResponseAssertions::assertForCase($response, $case);

        if ($case['expected']['status'] === 200) {
            assertDatabaseHas('settings', ['key' => 'appearance.general']);
        } else {
            assertDatabaseMissing('settings', ['key' => 'appearance.general']);
        }

        if ($isUploadCase && $case['expected']['status'] === 200) {
            expect($storedPath)
                ->toBeString()
                ->toStartWith('settings/logo-');
            expect(File::exists(public_path($storedPath)))->toBeTrue();
            assertDatabaseHas('settings', [
                'key' => 'appearance.logo_light',
                'value' => $storedPath,
            ]);
        }

        if (in_array('existing_logo', $case['preconditions'], true)) {
            assertDatabaseHas('settings', [
                'key' => 'appearance.logo_light',
                'value' => 'settings/existing-logo.png',
            ]);
        }
    } finally {
        if ($isUploadCase && is_string($storedPath)) {
            File::delete(public_path($storedPath));
        }
    }
})->with(TestData::load('admin/appearance/update.json'));
