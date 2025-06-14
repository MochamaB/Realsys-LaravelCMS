<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixMediaPermissions extends Command
{
    protected $signature = 'media:fix-permissions';
    protected $description = 'Fix permissions for media storage';

    public function handle()
    {
        $mediaPath = storage_path('app/public/media');

        if (!file_exists($mediaPath)) {
            $this->error('Media directory does not exist!');
            return 1;
        }

        $this->info('Fixing media storage permissions...');

        // Fix directory permissions
        $this->fixDirectoryPermissions($mediaPath);

        $this->info('Media storage permissions have been fixed!');
        return 0;
    }

    protected function fixDirectoryPermissions($path)
    {
        $items = new \FilesystemIterator($path);

        foreach ($items as $item) {
            if ($item->isDir()) {
                chmod($item->getPathname(), 0755);
                $this->fixDirectoryPermissions($item->getPathname());
            } else {
                chmod($item->getPathname(), 0644);
            }
        }
    }
} 