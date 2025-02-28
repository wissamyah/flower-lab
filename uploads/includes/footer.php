</main>

    <footer class="bg-white mt-auto border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-sm text-gray-500">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-primary-dark">
                        <i data-lucide="instagram" class="h-5 w-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-primary-dark">
                        <i data-lucide="facebook" class="h-5 w-5"></i>
                    </a>
                    <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" class="text-gray-400 hover:text-primary-dark">
                        <i data-lucide="message-circle" class="h-5 w-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    <script>
    // Emergency fix for invisible icons
    document.addEventListener('DOMContentLoaded', function() {
        // Check if profile icon has visible content
        const profileIcon = document.getElementById('profile-icon');
        if (profileIcon) {
            const iconContent = profileIcon.innerHTML.trim();
            if (iconContent === '' || 
                (profileIcon.querySelector('[data-lucide="user"]') && 
                !profileIcon.querySelector('svg'))) {
                
                // Inject SVG directly
                profileIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
            }
        }
    });
    </script>
    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>
    
    <!-- Custom JavaScript -->
    <script src="/flower-lab/scripts.js"></script>
    
    <!-- Notification System -->
    <script src="/flower-lab/notifications.js"></script>
    <script src="/flower-lab/utility.js"></script>

</body>
</html>