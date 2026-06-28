<?php

namespace Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExportProjectCommandTest extends TestCase
{
    protected string $tempDirName = 'tests/temp_export_test';
    protected string $tempDir;
    protected string $outputFileName = 'tests/temp_export_test/export.md';
    protected string $outputFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tempDir = base_path($this->tempDirName);
        $this->outputFile = base_path($this->outputFileName);
        
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        
        File::makeDirectory($this->tempDir, 0755, true);
        File::makeDirectory($this->tempDir . '/subdir', 0755, true);
        File::makeDirectory($this->tempDir . '/excluded', 0755, true);
        
        // PHP file with comments to test minification
        File::put($this->tempDir . '/test.php', "<?php\n// This is a comment\n\$x = 1;\n/* Block comment */\nreturn \$x;");
        
        // JSON file to test minification
        File::put($this->tempDir . '/test.json', "{\n  \"hello\": \"world\"\n}");
        
        // JS file to test lexical minification
        File::put($this->tempDir . '/test.js', "console.log('test'); // comment\n/* block */ let a = 1;");
        
        // .gitignore and ignored files
        File::put($this->tempDir . '/.gitignore', "ignored.txt\nignore_dir/\n");
        File::put($this->tempDir . '/ignored.txt', "This should be ignored");
        
        File::makeDirectory($this->tempDir . '/ignore_dir', 0755, true);
        File::put($this->tempDir . '/ignore_dir/secret.php', "<?php return 'secret';");
        
        // File for exclude argument testing
        File::put($this->tempDir . '/excluded/file.txt', "Exclude me");
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_export_project_command_minifies_and_ignores_correctly()
    {
        $this->artisan('project:export', [
            'directory' => $this->tempDirName,
            '--output' => $this->outputFileName
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputFile));
        $content = File::get($this->outputFile);

        // Check Architecture Map
        $this->assertStringContainsString('├── test.php', $content);

        // Check PHP minification
        $this->assertStringContainsString("<?php \$x = 1; return \$x;", $content);
        $this->assertStringNotContainsString("// This is a comment", $content);
        $this->assertStringNotContainsString("/* Block comment */", $content);

        // Check JSON minification
        $this->assertStringContainsString('{"hello":"world"}', $content);

        // Check JS minification
        $this->assertStringContainsString("console.log('test'); let a = 1;", $content);

        // Check ignored file CONTENTS are excluded (the filename may appear in .gitignore export)
        $this->assertStringNotContainsString('This should be ignored', $content);
        $this->assertStringNotContainsString("return 'secret';", $content);
        // Check ignored files don't appear as exported file entries in the tree
        $this->assertStringNotContainsString('├── ignored.txt', $content);
        $this->assertStringNotContainsString('└── ignored.txt', $content);
    }

    public function test_export_project_command_no_minify()
    {
        $this->artisan('project:export', [
            'directory' => $this->tempDirName,
            '--output' => $this->outputFileName,
            '--no-minify' => true
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputFile));
        $content = File::get($this->outputFile);

        // Comments should be preserved
        $this->assertStringContainsString("// This is a comment", $content);
        $this->assertStringContainsString("/* Block comment */", $content);
    }

    public function test_export_project_command_exclude_option()
    {
        $this->artisan('project:export', [
            'directory' => $this->tempDirName,
            '--output' => $this->outputFileName,
            '--exclude' => 'excluded'
        ])->assertExitCode(0);

        $content = File::get($this->outputFile);

        // Explicitly excluded dir should not be in the export
        $this->assertStringNotContainsString('excluded/file.txt', $content);
        $this->assertStringNotContainsString('Exclude me', $content);
    }

    public function test_export_project_command_invalid_directory()
    {
        $this->artisan('project:export', [
            'directory' => 'non_existent_directory_12345'
        ])
        ->expectsOutputToContain('Directory not found')
        ->assertExitCode(1);
    }
}
