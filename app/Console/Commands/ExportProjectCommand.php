<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class ExportProjectCommand extends Command
{
    protected $signature = 'project:export
                            {directory? : The specific directory to export (e.g., app/Services).}
                            {--output=project_export.md : The name of the output file (defaults to Markdown).}
                            {--exclude= : Comma-separated list of additional directories to exclude.}
                            {--no-minify : Skip stripping whitespace and comments from files.}';

    protected $description = 'Creates an LLM-optimized export with strict ignore file enforcement, precise timestamps, and single-line ultra-minification.';

    private array $binaryExtensions = [
        'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'webp',
        'pdf', 'zip', 'tar', 'gz', 'rar', '7z',
        'mp4', 'mp3', 'wav', 'ogg',
        'woff', 'woff2', 'ttf', 'eot',
        'sqlite', 'db', 'bin', 'exe', 'dll', 'so'
    ];

    private array $extensionToMarkdownLanguage = [
        'php' => 'php',
        'js' => 'javascript',
        'ts' => 'typescript',
        'vue' => 'vue',
        'css' => 'css',
        'scss' => 'scss',
        'html' => 'html',
        'json' => 'json',
        'yml' => 'yaml',
        'yaml' => 'yaml',
        'xml' => 'xml',
        'sql' => 'sql',
        'md' => 'markdown',
        'sh' => 'bash',
    ];

    public function handle()
    {
        $this->info("🚀 Initializing pure context-aware project export...");

        $rootPath = base_path();
        $targetDirectory = $this->argument('directory') ? base_path($this->argument('directory')) : $rootPath;
        $outputFile = base_path($this->option('output'));
        $additionalExcludes = array_filter(explode(',', (string) $this->option('exclude')));

        if (!is_dir($targetDirectory)) {
            $this->error("Directory not found: {$targetDirectory}");
            return 1;
        }

        $ignorePatterns = array_merge(
            $this->parseIgnoreFile($targetDirectory . '/.gitignore'),
            $this->parseIgnoreFile($targetDirectory . '/.dockerignore')
        );

        $finder = new Finder();
        $finder->files()
            ->in($targetDirectory)
            ->ignoreDotFiles(false)
            ->exclude(array_merge(['vendor', 'node_modules', 'docker', 'storage', 'bootstrap/cache', '.git', '.idea', '.vscode', 'public/build'], $additionalExcludes))
            ->filter(function (\SplFileInfo $file) use ($ignorePatterns) {
                if ($this->matchesIgnorePattern($file->getRelativePathname(), $ignorePatterns)) {
                    return false;
                }
                return !in_array(strtolower($file->getExtension()), $this->binaryExtensions);
            });

        $this->info("🔍 Scanning filesystem... (Single-core I/O check)");
        $paths = [];
        $scanCount = 0;

        foreach ($finder as $file) {
            $paths[] = $file->getRelativePathname();
            $scanCount++;
            
            if ($scanCount % 500 === 0) {
                $this->line("   ... found {$scanCount} valid files so far.");
            }
        }

        $fileCount = count($paths);
        if ($fileCount === 0) {
            $this->warn("No valid text files found to export.");
            return 0;
        }

        $handle = fopen($outputFile, 'w');
        if (!$handle) {
            $this->error("Failed to open stream for writing.");
            return 1;
        }

        $timestamp = now()->format('Y-m-d H:i:s T');

        fwrite($handle, "# Project Export\n");
        fwrite($handle, "**Generated At:** `{$timestamp}`\n\n");
        fwrite($handle, "## Architecture Map\n```text\n");
        fwrite($handle, $this->buildDirectoryTree($paths) . "```\n\n");
        fwrite($handle, "## Source Code\n\n");

        $this->info("\n✍️  Writing structure and executing single-line ultra-minification on {$fileCount} files...");
        $progressBar = $this->output->createProgressBar($fileCount);
        $progressBar->start();

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $extension = strtolower($file->getExtension());
            $mdLang = $this->extensionToMarkdownLanguage[$extension] ?? 'text';
            
            $content = $this->option('no-minify') 
                ? $file->getContents() 
                : $this->processWithInterpreter($file);

            $block = "### File: `{$relativePath}`\n```{$mdLang}\n{$content}\n```\n\n";
            fwrite($handle, $block);
            $progressBar->advance();
        }

        fclose($handle);
        $progressBar->finish();

        $this->info("\n\n✅ Export complete. Code safely analyzed, crushed to single lines, and streamed to '{$outputFile}'.");
        return 0;
    }

    private function parseIgnoreFile(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $patterns = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $patterns[] = $line;
        }

        return $patterns;
    }

    private function matchesIgnorePattern(string $relativePath, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $pattern = ltrim($pattern, '/');
            $isDirectoryMatch = str_ends_with($pattern, '/');
            $cleanPattern = rtrim($pattern, '/');

            if ($isDirectoryMatch && str_starts_with($relativePath . '/', $cleanPattern . '/')) {
                return true;
            }

            if ($relativePath === $cleanPattern || str_ends_with($relativePath, '/' . $cleanPattern)) {
                return true;
            }

            if (str_contains($cleanPattern, '*') || str_contains($cleanPattern, '?')) {
                if (fnmatch($cleanPattern, $relativePath) || fnmatch('*/' . $cleanPattern, $relativePath) || fnmatch($cleanPattern . '/*', $relativePath)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function buildDirectoryTree(array $paths): string
    {
        $tree = [];
        foreach ($paths as $path) {
            $parts = explode(DIRECTORY_SEPARATOR, $path);
            $current = &$tree;
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
        return $this->renderTree($tree);
    }

    private function renderTree(array $tree, string $prefix = ''): string
    {
        $output = '';
        $keys = array_keys($tree);
        $lastIndex = count($keys) - 1;

        foreach ($keys as $index => $key) {
            $isLast = ($index === $lastIndex);
            $output .= $prefix . ($isLast ? '└── ' : '├── ') . $key . "\n";
            if (!empty($tree[$key])) {
                $output .= $this->renderTree($tree[$key], $prefix . ($isLast ? '    ' : '│   '));
            }
        }
        return $output;
    }

    private function processWithInterpreter(\SplFileInfo $file): string
    {
        $extension = strtolower($file->getExtension());
        $content = $file->getContents();

        if ($extension === 'php') {
            return $this->interpretPhp($content);
        }

        if (in_array($extension, ['js', 'ts', 'vue', 'css', 'scss', 'html'])) {
            return $this->interpretLexically($content);
        }

        if ($extension === 'json') {
            $decoded = json_decode($content);
            return $decoded ? json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : trim($content);
        }

        // For plain text, markdown, yaml, we should probably just trim the edges, 
        // as crushing YAML to one line breaks it completely.
        if (in_array($extension, ['yml', 'yaml', 'md'])) {
            return trim($content);
        }

        return trim($content);
    }

    private function interpretPhp(string $content): string
    {
        $tokens = token_get_all($content);
        $minified = '';
        $lastChar = '';

        foreach ($tokens as $token) {
            if (is_string($token)) {
                $minified .= $token;
                $lastChar = $token;
            } else {
                $id = $token[0];
                $text = $token[1];
                
                if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
                    continue;
                }

                if ($id === T_OPEN_TAG) {
                    $minified .= '<?php ';
                    $lastChar = ' ';
                    continue;
                }

                if ($id === T_WHITESPACE) {
                    // Ultra-crush: Treat all newlines and spaces as a single horizontal space
                    if ($lastChar !== ' ') {
                        $minified .= ' ';
                        $lastChar = ' ';
                    }
                    continue;
                }
                
                $minified .= $text;
                $lastChar = substr($text, -1);
            }
        }

        return trim($minified);
    }

    private function interpretLexically(string $content): string
    {
        $length = strlen($content);
        $out = '';
        
        $inString = false;
        $stringChar = '';
        $inBlockComment = false;
        $inLineComment = false;
        $lastAppended = '';

        for ($i = 0; $i < $length; $i++) {
            $c = $content[$i];
            $next = $i + 1 < $length ? $content[$i + 1] : '';
            $prev = $i > 0 ? $content[$i - 1] : '';

            if (!$inBlockComment && !$inLineComment) {
                if (($c === '"' || $c === "'" || $c === '`') && $prev !== '\\') {
                    if (!$inString) {
                        $inString = true;
                        $stringChar = $c;
                    } elseif ($inString && $c === $stringChar) {
                        $inString = false;
                    }
                }
            }

            if (!$inString) {
                if (!$inBlockComment && $c === '/' && $next === '/') {
                    $inLineComment = true;
                    $i++; 
                    continue;
                }
                if ($inLineComment) {
                    // Line comment ends at newline, but we replace the newline with a space
                    if ($c === "\n" || $c === "\r") {
                        $inLineComment = false;
                        if ($lastAppended !== ' ') {
                            $out .= ' ';
                            $lastAppended = ' ';
                        }
                    }
                    continue;
                }

                if (!$inLineComment && $c === '/' && $next === '*') {
                    $inBlockComment = true;
                    $i++; 
                    continue;
                }
                if ($inBlockComment) {
                    if ($c === '*' && $next === '/') {
                        $inBlockComment = false;
                        $i++; 
                    }
                    continue;
                }

                // Ultra-crush: Treat all structural spacing (\n, \r, \t, space) identically
                if ($c === "\n" || $c === "\r" || $c === "\t" || $c === ' ') {
                    if ($lastAppended !== ' ') {
                        $out .= ' ';
                        $lastAppended = ' ';
                    }
                    continue;
                }
            }

            $out .= $c;
            $lastAppended = $c;
        }

        return trim($out);
    }
}