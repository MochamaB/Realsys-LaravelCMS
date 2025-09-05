<div class="realsys-loader" id="globalLoader" style="display: none;">
    <img src="{{ asset('images/realsyslogoclear.png') }}" 
         alt="Realsys Solutions" 
         class="loader-logo">
    <div class="loader-progress">
        <div class="loader-progress-bar"></div>
    </div>
</div>

@once
    @push('styles')
    <style>
        /* Loader container */
        .realsys-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Logo */
        .realsys-loader .loader-logo {
            max-width: 180px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite ease-in-out;
        }

        /* Progress bar container */
        .realsys-loader .loader-progress {
            width: 150px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }

        /* Animated progress */
        .realsys-loader .loader-progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #f9b233, #6a1b9a);
            border-radius: 2px;
            animation: progressAnim 2s infinite;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        @keyframes progressAnim {
            0% { width: 0%; }
            50% { width: 100%; }
            100% { width: 0%; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function showLoader() {
            document.getElementById("globalLoader").style.display = "flex";
        }

        function hideLoader() {
            document.getElementById("globalLoader").style.display = "none";
        }
    </script>
    @endpush
@endonce
