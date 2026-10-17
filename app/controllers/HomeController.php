<?php
/**
 * HomeController — หน้าแรก (Landing Page)
 */
class HomeController extends Controller
{
    public function index(): void
    {
        // ถ้า Login แล้ว ไปหน้า Dashboard
        if (Auth::check()) {
            App::redirect('dashboard');
            return;
        }
        $this->view('home', [], 'auth');
    }
}
