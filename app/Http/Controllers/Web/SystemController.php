<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use ZipArchive;

class SystemController extends Controller
{
    public function monitoring()
    {
        // 1. Cek Status Server Node.js (Port 3000)
        $nodeStatus = false;
        if ($fp = @fsockopen('127.0.0.1', 3000, $errCode, $errStr, 1)) {
            $nodeStatus = true;
            fclose($fp);
        }

        // 2. Cek Storage Drive Lokal tempat XAMPP berada
        $diskTotal = disk_total_space(base_path());
        $diskFree = disk_free_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

        // Mengubah satuan Bytes ke Gigabytes (GB)
        $diskTotalGB = round($diskTotal / 1073741824, 2);
        $diskFreeGB = round($diskFree / 1073741824, 2);
        
        // 3. Cek Memory Usage Framework Laravel
        $phpMemory = round(memory_get_usage() / 1048576, 2); // dalam MB

        return view('system.monitoring', compact('nodeStatus', 'diskTotalGB', 'diskFreeGB', 'diskUsagePct', 'phpMemory'));
    }

    public function backupSessions()
    {
        // Fitur Backup: Mengompres folder storage/sessions menjadi .zip
        $zip = new ZipArchive;
        $backupDir = storage_path('backups');
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $fileName = 'RDS_BOT_Sessions_' . date('Y_m_d_His') . '.zip';
        $filePath = $backupDir . '/' . $fileName;

        if ($zip->open($filePath, ZipArchive::CREATE) === TRUE) {
            $sessionDir = storage_path('sessions');
            
            if (File::exists($sessionDir)) {
                $files = File::allFiles($sessionDir);
                foreach ($files as $file) {
                    $relativePath = str_replace($sessionDir . '/', '', $file->getPathname());
                    $zip->addFile($file->getPathname(), $relativePath);
                }
            }
            $zip->close();

            // Langsung download file ZIP-nya ke browser admin
            return response()->download($filePath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file backup. Pastikan module ZipArchive PHP aktif di XAMPP.');
    }
}