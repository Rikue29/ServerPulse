<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Analysis Report - Log #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 1in;
            @top-center {
                content: "ServerPulse Infrastructure Analysis Report";
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #666;
            }
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #666;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
          .company-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-image {
            width: 160px;
            height: auto;
            margin: 0 auto 10px auto;
            display: block;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin: 0;
        }
        
        .company-tagline {
            font-size: 12px;
            color: #6b7280;
            margin: 5px 0 0 0;
        }
        
        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            text-align: center;
            margin: 20px 0;
        }
        
        .report-meta {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .report-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-meta td {
            padding: 5px;
            font-size: 12px;
        }
        
        .report-meta .label {
            font-weight: bold;
            color: #374151;
            width: 120px;
        }
        
        .alert-banner {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        
        .alert-error {
            background: #fee2e2;
            border: 2px solid #dc2626;
            color: #991b1b;
        }
        
        .alert-warning {
            background: #fef3c7;
            border: 2px solid #d97706;
            color: #92400e;
        }
        
        .alert-info {
            background: #dbeafe;
            border: 2px solid #2563eb;
            color: #1d4ed8;
        }
        
        .section {
            margin: 25px 0;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .two-column {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            color: #374151;
        }
        
        .metrics-grid {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .metric-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .metric-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .critical { color: #dc2626; }
        .warning { color: #d97706; }
        .normal { color: #059669; }
        
        .timeline-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 12px;
        }
        
        .timeline-table th,
        .timeline-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        
        .timeline-table th {
            background: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        
        .recommendations {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .recommendations h3 {
            color: #1e40af;
            margin: 0 0 15px 0;
        }
        
        .recommendations ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .recommendations li {
            margin: 8px 0;
            line-height: 1.4;
        }
        
        .footer {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 40px;
            font-size: 10px;
            color: #6b7280;
        }
        
        .footer table {
            width: 100%;
        }
        
        .footer td {
            vertical-align: top;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .technical-details {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 11px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        .badge-success { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">        <div class="company-logo">
            <img src="{{ public_path('images/serverpulse-logo.svg') }}" alt="ServerPulse" class="logo-image">
            <h1 class="company-name">ServerPulse</h1>
            <p class="company-tagline">Server Monitoring & Analysis Platform</p>
        </div>
        
        <h2 class="report-title">SYSTEM ANALYSIS REPORT</h2>
        
        <div class="report-meta">
            <table>
                <tr>
                    <td class="label">Report ID:</td>
                    <td>RPT-{{ date('Ymd') }}-{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td class="label">Classification:</td>
                    <td>{{ $log->level == 'error' ? 'CRITICAL' : ($log->level == 'warning' ? 'STANDARD' : 'INFORMATIONAL') }}</td>
                </tr>
                <tr>
                    <td class="label">Incident ID:</td>
                    <td>#{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td class="label">Generated:</td>
                    <td>{{ now()->format('F j, Y \a\t g:i A T') }}</td>
                </tr>
                <tr>
                    <td class="label">Server:</td>
                    <td>{{ $log->server->name ?? 'Unknown' }}</td>
                    <td class="label">Event Time:</td>
                    <td>{{ $log->created_at->format('F j, Y \a\t g:i:s A T') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Alert Level Banner -->
    <div class="alert-banner {{ $log->level == 'error' ? 'alert-error' : ($log->level == 'warning' ? 'alert-warning' : 'alert-info') }}">
        <h2 style="margin: 0; font-size: 20px;">
            {{ $log->level == 'error' ? 'CRITICAL SYSTEM ALERT' : ($log->level == 'warning' ? 'PERFORMANCE WARNING' : 'SYSTEM INFORMATION') }}
        </h2>
        <p style="margin: 5px 0 0 0; font-size: 14px;">
            {{ $log->level == 'error' ? 'Immediate attention required' : ($log->level == 'warning' ? 'Monitoring and investigation recommended' : 'Informational event logged') }}
        </p>
    </div>

    <!-- Executive Summary -->
    <div class="section">
        <h3 class="section-title">EXECUTIVE SUMMARY</h3>
        <p style="font-size: 14px; line-height: 1.8; text-align: justify;">
            @if($log->level == 'error')
                <strong>CRITICAL INFRASTRUCTURE INCIDENT:</strong> Our automated monitoring systems have detected a critical error condition on server <strong>{{ $log->server->name ?? 'Unknown' }}</strong> ({{ $log->server->ip_address ?? 'IP not available' }}) at {{ $log->created_at->format('g:i A \o\n F j, Y') }}. This incident has been classified as high-priority and requires immediate technical intervention to prevent potential service disruption and maintain system reliability.
                
                <br><br>The error originated from the {{ ucfirst($log->source) }} subsystem and was automatically detected by our continuous monitoring protocols. Based on preliminary analysis, this condition may impact system performance and could potentially affect user experience if left unresolved. Immediate escalation to the on-call engineering team has been recommended.
            @elseif($log->level == 'warning')
                <strong>PERFORMANCE MONITORING ALERT:</strong> Our monitoring infrastructure has identified a warning condition on server <strong>{{ $log->server->name ?? 'Unknown' }}</strong> ({{ $log->server->ip_address ?? 'IP not available' }}) at {{ $log->created_at->format('g:i A \o\n F j, Y') }}. While this condition does not pose an immediate threat to system operations, it indicates a potential area of concern that warrants monitoring and possible optimization.
                
                <br><br>The alert was triggered by the {{ ucfirst($log->source) }} monitoring component and suggests that system metrics have crossed predetermined threshold levels. Proactive investigation and corrective measures may prevent this condition from escalating to a more serious state.
            @else
                <strong>OPERATIONAL STATUS UPDATE:</strong> An informational event has been recorded for server <strong>{{ $log->server->name ?? 'Unknown' }}</strong> ({{ $log->server->ip_address ?? 'IP not available' }}) at {{ $log->created_at->format('g:i A \o\n F j, Y') }}. This event represents normal system operations and has been logged for audit trail, compliance, and analytical purposes.
                
                <br><br>The information was captured by the {{ ucfirst($log->source) }} monitoring system as part of routine operational logging. This data contributes to our comprehensive system health assessment and helps maintain detailed records of infrastructure performance patterns.
            @endif
        </p>
    </div>

    <!-- Technical Analysis -->
    <div class="section">
        <h3 class="section-title">TECHNICAL ANALYSIS</h3>
        
        <div class="two-column">
            <div class="column">
                <div class="info-box">
                    <h4>Event Details</h4>
                    <table style="width: 100%; font-size: 12px;">
                        <tr><td><strong>Event ID:</strong></td><td>#{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                        <tr><td><strong>Timestamp:</strong></td><td>{{ $log->created_at->format('M j, Y g:i:s A T') }}</td></tr>
                        <tr><td><strong>Severity:</strong></td><td><span class="badge badge-{{ $log->level == 'error' ? 'error' : ($log->level == 'warning' ? 'warning' : 'info') }}">{{ ucfirst($log->level) }}</span></td></tr>
                        <tr><td><strong>Source:</strong></td><td>{{ ucfirst($log->source) }}</td></tr>
                    </table>
                </div>
            </div>
            
            <div class="column">
                <div class="info-box">
                    <h4>Server Information</h4>
                    @if($log->server)
                        <table style="width: 100%; font-size: 12px;">
                            <tr><td><strong>Server Name:</strong></td><td>{{ $log->server->name }}</td></tr>
                            <tr><td><strong>IP Address:</strong></td><td>{{ $log->server->ip_address }}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge badge-{{ $log->server->status == 'online' ? 'success' : 'error' }}">{{ ucfirst($log->server->status) }}</span></td></tr>
                            <tr><td><strong>Server ID:</strong></td><td>{{ $log->server->id }}</td></tr>
                        </table>
                    @else
                        <p style="color: #6b7280; font-style: italic;">Server information not available</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="technical-details">
            <h4 style="margin: 0 0 10px 0; color: #374151;">Event Message:</h4>
            <div style="background: #ffffff; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px;">
                {{ $log->message }}
            </div>
        </div>

        @if($log->context)
        <div class="technical-details">
            <h4 style="margin: 0 0 10px 0; color: #374151;">Context Data:</h4>
            <div style="background: #ffffff; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; white-space: pre-wrap;">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
        </div>
        @endif
    </div>

    <!-- Performance Metrics -->
    @if($log->context && (isset($log->context['cpu_usage']) || isset($log->context['memory_usage']) || isset($log->context['disk_usage'])))
    <div class="section">
        <h3 class="section-title">PERFORMANCE METRICS AT TIME OF EVENT</h3>
        
        <div class="metrics-grid">
            @if(isset($log->context['cpu_usage']))
            <div class="metric-item">
                <div class="metric-label">CPU Usage</div>
                <div class="metric-value {{ $log->context['cpu_usage'] > 80 ? 'critical' : ($log->context['cpu_usage'] > 60 ? 'warning' : 'normal') }}">
                    {{ $log->context['cpu_usage'] }}%
                </div>
                <div style="font-size: 10px; color: #6b7280;">
                    {{ $log->context['cpu_usage'] > 80 ? 'CRITICAL' : ($log->context['cpu_usage'] > 60 ? 'WARNING' : 'NORMAL') }}
                </div>
            </div>
            @endif

            @if(isset($log->context['memory_usage']))
            <div class="metric-item">
                <div class="metric-label">Memory Usage</div>
                <div class="metric-value {{ $log->context['memory_usage'] > 90 ? 'critical' : ($log->context['memory_usage'] > 70 ? 'warning' : 'normal') }}">
                    {{ $log->context['memory_usage'] }}%
                </div>
                <div style="font-size: 10px; color: #6b7280;">
                    {{ $log->context['memory_usage'] > 90 ? 'CRITICAL' : ($log->context['memory_usage'] > 70 ? 'WARNING' : 'NORMAL') }}
                </div>
            </div>
            @endif

            @if(isset($log->context['disk_usage']))
            <div class="metric-item">
                <div class="metric-label">Disk Usage</div>
                <div class="metric-value {{ $log->context['disk_usage'] > 85 ? 'critical' : ($log->context['disk_usage'] > 70 ? 'warning' : 'normal') }}">
                    {{ $log->context['disk_usage'] }}%
                </div>
                <div style="font-size: 10px; color: #6b7280;">
                    {{ $log->context['disk_usage'] > 85 ? 'CRITICAL' : ($log->context['disk_usage'] > 70 ? 'WARNING' : 'NORMAL') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Page Break for Long Reports -->
    <div class="page-break"></div>

    <!-- Related Events Timeline -->
    @if($relatedLogs->count() > 0)
    <div class="section">
        <h3 class="section-title">RELATED EVENTS TIMELINE (±30 MINUTES)</h3>
        
        <table class="timeline-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Severity</th>
                    <th>Source</th>
                    <th>Event Message</th>
                    <th>Correlation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($relatedLogs as $relatedLog)
                <tr>
                    <td>{{ $relatedLog->created_at->format('H:i:s') }}</td>
                    <td><span class="badge badge-{{ $relatedLog->level == 'error' ? 'error' : ($relatedLog->level == 'warning' ? 'warning' : 'info') }}">{{ ucfirst($relatedLog->level) }}</span></td>
                    <td>{{ $relatedLog->source }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($relatedLog->message, 60) }}</td>
                    <td>{{ $relatedLog->created_at < $log->created_at ? 'Before' : 'After' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recommendations -->
    <div class="section">
        <h3 class="section-title">RECOMMENDATIONS & ACTION PLAN</h3>
        
        <div class="recommendations">
            <h3>Immediate Response Protocol (0-4 hours)</h3>
            <ul>
                @if($log->level == 'error')
                    <li><strong>Escalate immediately:</strong> Contact on-call engineer and incident response team</li>
                    <li><strong>Service health check:</strong> Verify user-facing services and customer impact assessment</li>
                    <li><strong>Emergency procedures:</strong> Activate incident management protocol and stakeholder notification</li>
                    <li><strong>Containment strategy:</strong> Implement failover procedures if available</li>
                @elseif($log->level == 'warning')
                    <li><strong>Assignment:</strong> Schedule investigation with appropriate technical team within 2-4 hours</li>
                    <li><strong>Enhanced monitoring:</strong> Implement additional alerting for similar conditions</li>
                    <li><strong>Threshold analysis:</strong> Review alert sensitivity and adjust if necessary</li>
                    <li><strong>Trend evaluation:</strong> Analyze historical patterns for similar warnings</li>
                @else
                    <li><strong>No immediate action required:</strong> Event logged for informational purposes</li>
                    <li><strong>Continuous monitoring:</strong> Maintain regular observation schedule</li>
                    <li><strong>Data collection:</strong> Archive event data for analytical purposes</li>
                    <li><strong>Compliance logging:</strong> Ensure audit trail requirements are met</li>
                @endif
            </ul>
        </div>

        <div class="recommendations">
            <h3>Strategic Improvement Plan (1-4 weeks)</h3>
            <ul>
                @if($log->level == 'error')
                    <li><strong>Root cause analysis:</strong> Conduct comprehensive investigation to identify underlying causes</li>
                    <li><strong>Infrastructure hardening:</strong> Implement redundancy and failover mechanisms</li>
                    <li><strong>Process enhancement:</strong> Update incident response procedures and team training</li>
                    <li><strong>Documentation update:</strong> Revise troubleshooting guides and operational runbooks</li>
                    <li><strong>Monitoring optimization:</strong> Enhance early warning systems and alert granularity</li>
                @else
                    <li><strong>Trend analysis:</strong> Establish baseline patterns and identify optimization opportunities</li>
                    <li><strong>Capacity planning:</strong> Use performance data for future resource allocation decisions</li>
                    <li><strong>Process automation:</strong> Implement automated responses for routine events</li>
                    <li><strong>Performance optimization:</strong> Fine-tune system configurations based on usage patterns</li>
                    <li><strong>Knowledge management:</strong> Document insights and learnings for team knowledge base</li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Success Metrics -->
    <div class="section">
        <h3 class="section-title">SUCCESS METRICS & FOLLOW-UP</h3>
        
        <div class="two-column">
            <div class="column">
                <div class="info-box">
                    <h4>Key Performance Indicators</h4>
                    <ul style="font-size: 12px; margin: 0; padding-left: 20px;">
                        @if($log->level == 'error')
                            <li>Time to resolution (Target: &lt; 1 hour for critical issues)</li>
                            <li>Service availability restoration (Target: 99.9% uptime)</li>
                            <li>Customer impact assessment completion</li>
                            <li>Post-incident review and documentation</li>
                        @else
                            <li>Event response time (Target: &lt; 4 hours for warnings)</li>
                            <li>Trend analysis completion within 24 hours</li>
                            <li>Proactive optimization implementation</li>
                            <li>Continuous monitoring effectiveness assessment</li>
                        @endif
                    </ul>
                </div>
            </div>
            
            <div class="column">
                <div class="info-box">
                    <h4>Follow-up Schedule</h4>
                    <ul style="font-size: 12px; margin: 0; padding-left: 20px;">
                        <li><strong>24 hours:</strong> Initial response verification and status update</li>
                        <li><strong>1 week:</strong> Implementation progress review</li>
                        <li><strong>1 month:</strong> Effectiveness assessment and fine-tuning</li>
                        <li><strong>Quarterly:</strong> Strategic review and continuous improvement evaluation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong>Report Classification:</strong> {{ $log->level == 'error' ? 'Critical' : ($log->level == 'warning' ? 'Standard' : 'Informational') }}<br>
                    <strong>Retention Period:</strong> 7 years as per compliance requirements<br>
                    <strong>Distribution:</strong> IT Operations, Management, Compliance Team
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Generated by:</strong> ServerPulse Monitoring System v2.0<br>
                    <strong>Report ID:</strong> RPT-{{ date('Ymd') }}-{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Generated on:</strong> {{ now()->format('F j, Y \a\t g:i A T') }}
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
            <em>This report is automatically generated by the ServerPulse Infrastructure Monitoring Platform. For questions or concerns, contact the IT Operations team.</em>
        </div>
    </div>
</body>
</html>
