<footer class="footer mt-auto">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <h5 class="font-weight-bold mb-2" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">🏋️ Project Rock Ascent</h5>
                <p class="text-muted small mb-0">Elevating your fitness journey with premium management solutions.</p>
            </div>
            <div class="col-md-6 text-md-right">
                <div class="social-links mb-2">
                    <a href="https://www.facebook.com" target="_blank" class="text-muted mr-3 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com" target="_blank" class="text-muted mr-3 social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.twitter.com" target="_blank" class="text-muted mr-3 social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com" target="_blank" class="text-muted social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <p class="text-muted small mb-0">&copy; 2026 Project Rock Ascent. Premium Gym Management.</p>
            </div>
        </div>
    </div>
</footer>

<?php 
// Show chatbot for logged in users
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['instructor_id'])) {
    include('chatbot.php'); 
}
?>
