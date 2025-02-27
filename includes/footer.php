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

    <!-- Initialize Lucide icons -->
    <script>
        lucide.createIcons();
    </script>
    
    <!-- Custom JavaScript -->
    <script src="/flower-lab/scripts.js"></script>
    
    <!-- Notification System -->
    <script src="/flower-lab/notifications.js"></script>
</body>
</html>