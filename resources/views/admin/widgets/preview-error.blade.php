<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Preview Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 40px 20px;
            color: #495057;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .error-icon {
            font-size: 64px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #212529;
        }
        .error-message {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 24px;
            color: #6c757d;
        }
        .error-details {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 16px;
            text-align: left;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 14px;
            color: #495057;
            margin-top: 20px;
        }
        .widget-info {
            background: #e9ecef;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .widget-info strong {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Widget Preview Error</h1>
        <p class="error-message">
            There was an error loading the preview for this widget. This could be due to missing theme files, 
            invalid widget configuration, or other rendering issues.
        </p>
        
        <div class="widget-info">
            <strong>Widget:</strong> {{ $widget->name ?? 'Unknown' }} ({{ $widget->slug ?? 'unknown' }})<br>
            <strong>Theme:</strong> {{ $widget->theme->name ?? 'Unknown' }} ({{ $widget->theme->slug ?? 'unknown' }})
        </div>
        
        @if(config('app.debug') && isset($error))
        <div class="error-details">
            <strong>Error Details:</strong><br>
            {{ $error }}
        </div>
        @endif
        
        <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
            Try refreshing the preview or check the widget configuration.
        </p>
    </div>
</body>
</html>
