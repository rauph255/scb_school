<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends Controller
{
    public function index(AdminExperienceController $experience): Response
    {
        $this->authorizeAuditAccess();

        return $experience->render('admin-audit-log');
    }

    public function show(AuditLog $auditLog): View
    {
        $this->authorizeAuditAccess();
        $auditLog->load('actor');

        return view('admin.audit-log.show', [
            'activeNav' => 'audit-log',
            'auditLog' => $auditLog,
        ]);
    }

    public function export(): StreamedResponse
    {
        $this->authorizeAuditAccess();

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['ID', 'Action', 'Description', 'Actor', 'Subject type', 'Subject ID', 'Date', 'IP address', 'Request ID']);

            $logs = AuditLog::query()
                ->with('actor:id,name')
                ->lazyByIdDesc(500);

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->id,
                    $this->csvValue($log->action),
                    $this->csvValue($log->description),
                    $this->csvValue($log->actor?->name ?? 'System'),
                    $this->csvValue(class_basename((string) ($log->subject_type ?? 'System'))),
                    $log->subject_id,
                    $log->created_at?->toIso8601String(),
                    $this->csvValue($log->ip_address),
                    $this->csvValue($log->request_id),
                ]);
            }

            fclose($output);
        }, 'scb-audit-log-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeAuditAccess(): void
    {
        abort_unless(auth()->user()?->hasPermission('audit.view'), 403);
    }

    private function csvValue(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
