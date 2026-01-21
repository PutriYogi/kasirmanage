</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<script src="/vendor/admin/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/admin/dist/js/adminlte.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Fullscreen State Persistence -->
<script>
$(document).ready(function() {
    // Function to save current fullscreen state
    function saveFullscreenState() {
        const isFullscreen = document.fullscreenElement || 
                           document.webkitFullscreenElement || 
                           document.mozFullScreenElement ||
                           document.msFullscreenElement;
        localStorage.setItem('isFullscreen', isFullscreen ? 'true' : 'false');
    }
    
    // Function to restore fullscreen state
    function restoreFullscreenState() {
        const wasFullscreen = localStorage.getItem('isFullscreen') === 'true';
        if (wasFullscreen && !document.fullscreenElement) {
            // Try to enter fullscreen mode
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen().catch(err => console.log('Fullscreen request failed:', err));
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        }
    }
    
    // Restore fullscreen state when page loads
    restoreFullscreenState();
    
    // Listen for fullscreen button clicks
    $(document).on('click', '[data-widget="fullscreen"]', function(e) {
        // Let AdminLTE handle the click first, then save state
        setTimeout(() => {
            saveFullscreenState();
        }, 100);
    });
    
    // Listen for all fullscreen change events
    const fullscreenEvents = [
        'fullscreenchange',
        'webkitfullscreenchange', 
        'mozfullscreenchange',
        'MSFullscreenChange'
    ];
    
    fullscreenEvents.forEach(event => {
        document.addEventListener(event, saveFullscreenState, false);
    });
    
    // Listen for ESC key or other ways to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            setTimeout(saveFullscreenState, 100);
        }
    });
    
    // Fallback: Monitor fullscreen state periodically
    setInterval(() => {
        const currentState = localStorage.getItem('isFullscreen') === 'true';
        const actualState = !!(document.fullscreenElement || 
                             document.webkitFullscreenElement || 
                             document.mozFullScreenElement ||
                             document.msFullscreenElement);
        
        if (currentState !== actualState) {
            saveFullscreenState();
        }
    }, 1000);
});
</script>
</body>
</html>
