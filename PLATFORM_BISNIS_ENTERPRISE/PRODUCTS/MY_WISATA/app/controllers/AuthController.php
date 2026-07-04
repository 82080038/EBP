<?php
/**
 * MyWisata Application - Auth Controller
 * 
 * Handles authentication: login, register, logout.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class AuthController extends Controller {
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }
    
    /**
     * Login page
     */
    public function login() {
        // Redirect if already logged in
        if (Middleware::isAuthenticated()) {
            $this->redirect('dashboard');
        }
        
        $data = [
            'title' => 'Masuk - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Handle login submission
     */
    public function doLogin() {
        if (!$this->isAjax()) {
            $this->redirect('auth/login');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $email = $this->post('email');
        $password = $this->post('password');
        $remember = $this->post('remember') === 'true';
        
        // Validate input
        if (empty($email) || empty($password)) {
            $this->json(['status' => 'error', 'message' => 'Email dan password wajib diisi'], 400);
        }
        
        // Verify credentials
        $user = $this->userModel->verify($email, $password);
        
        if (!$user) {
            $this->json(['status' => 'error', 'message' => 'Email atau password salah'], 401);
        }
        
        // Set session
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('role', $user['role']);
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Set remember token if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            Session::set('remember_token', $token);
            // In production, store token in database and set cookie
        }
        
        // Redirect based on role
        $redirect = 'dashboard';
        if ($user['role'] === 'admin') {
            $redirect = 'admin/dashboard';
        } elseif ($user['role'] === 'tour_guide') {
            $redirect = 'tourguide/dashboard';
        }
        
        $this->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'redirect' => BASE_URL . $redirect
        ]);
    }
    
    /**
     * Register page
     */
    public function register() {
        // Redirect if already logged in
        if (Middleware::isAuthenticated()) {
            $this->redirect('dashboard');
        }
        
        $data = [
            'title' => 'Daftar - MyWisata',
            'csrf_token' => Middleware::csrfToken(),
            'role' => $this->get('role', 'wisatawan') // wisatawan or tour_guide
        ];
        
        $this->view('auth/register', $data);
    }
    
    /**
     * Handle registration submission
     */
    public function doRegister() {
        if (!$this->isAjax()) {
            $this->redirect('auth/register');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        $phone = $this->post('phone');
        $role = $this->post('role', 'wisatawan');
        
        // Validate input
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nama wajib diisi';
        }
        
        if (empty($email)) {
            $errors[] = 'Email wajib diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        if (empty($password)) {
            $errors[] = 'Password wajib diisi';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        if (!empty($errors)) {
            $this->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($email)) {
            $this->json(['status' => 'error', 'message' => 'Email sudah terdaftar'], 409);
        }
        
        // Register user
        $userId = $this->userModel->register([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => $role,
            'status' => 'active',
            'email_verified' => 0
        ]);
        
        if ($userId) {
            $this->json([
                'status' => 'success',
                'message' => 'Registrasi berhasil. Silakan login.',
                'redirect' => BASE_URL . 'auth/login'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mendaftar. Silakan coba lagi.'], 500);
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        Session::destroy();
        $this->redirect('auth/login');
    }
    
    /**
     * Forgot password page
     */
    public function forgotPassword() {
        $data = [
            'title' => 'Lupa Password - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('auth/forgot-password', $data);
    }
    
    /**
     * Handle forgot password
     */
    public function doForgotPassword() {
        if (!$this->isAjax()) {
            $this->redirect('auth/forgot-password');
        }
        
        $email = $this->post('email');
        
        if (empty($email)) {
            $this->json(['status' => 'error', 'message' => 'Email wajib diisi'], 400);
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists or not
            $this->json([
                'status' => 'success',
                'message' => 'Jika email terdaftar, link reset password akan dikirim.'
            ]);
        }
        
        // TODO: Send reset email with token
        // For now, just return success
        $this->json([
            'status' => 'success',
            'message' => 'Jika email terdaftar, link reset password akan dikirim.'
        ]);
    }
}
