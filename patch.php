<?php

/**
 * Jalankan SEKALI dari root proyek Laravel setelah menyalin file overlay:
 *   php patch.php
 */
$root = __DIR__;

// 1. config/app.php -> tambah force_https
$file = $root.'/config/app.php';
$content = file_get_contents($file);
if (! str_contains($content, 'force_https')) {
    $content = preg_replace(
        "/('name'\s*=>\s*env\('APP_NAME',\s*'Laravel'\),)/",
        "$1\n\n    'force_https' => (bool) env('FORCE_HTTPS', false),",
        $content,
        1,
        $count
    );
    if ($count === 0) {
        fwrite(STDERR, "Gagal otomatis: tambahkan manual  'force_https' => (bool) env('FORCE_HTTPS', false),  ke config/app.php\n");
    } else {
        file_put_contents($file, $content);
        echo "config/app.php diperbarui\n";
    }
}

// 2. .gitignore -> abaikan artefak uji
$ignore = ['coverage-*', 'coverage.txt', 'coverage.xml', 'junit.xml', 'events.txt', 'phpunit_*.txt',
    '.phpunit.cache', '.phpunit.result.cache', 'trivy*', 'latest-app.zip'];
$gitignore = $root.'/.gitignore';
$current = file_exists($gitignore) ? file_get_contents($gitignore) : '';
$missing = array_filter($ignore, fn ($line) => ! preg_match('/^'.preg_quote($line, '/').'$/m', $current));
if ($missing) {
    file_put_contents($gitignore, rtrim($current)."\n\n# Artefak pengujian / CI\n".implode("\n", $missing)."\n");
    echo ".gitignore diperbarui\n";
}

// 3. .env.example -> tambah variabel
$envExample = $root.'/.env.example';
$env = file_get_contents($envExample);
foreach (['FORCE_HTTPS' => 'false', 'OPENAI_API_KEY' => '', 'OPENAI_MODEL' => 'gpt-4o-mini', 'OPENAI_BASE_URL' => 'https://api.openai.com/v1'] as $key => $value) {
    if (! preg_match('/^'.$key.'=/m', $env)) {
        $env = rtrim($env)."\n{$key}={$value}\n";
    }
}
file_put_contents($envExample, $env);
echo ".env.example diperbarui\n";

// 4. Hapus test contoh bawaan (akan gagal karena '/' sekarang redirect)
foreach (['/tests/Feature/ExampleTest.php', '/tests/Unit/ExampleTest.php'] as $example) {
    if (file_exists($root.$example)) {
        unlink($root.$example);
        echo "dihapus: {$example}\n";
    }
}

echo "Selesai. Lanjut: php artisan migrate:fresh --seed\n";
