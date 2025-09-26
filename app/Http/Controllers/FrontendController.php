<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function showUserForm()
    {
        return view('karyawan.login');
    }
    public function showKaryawanDashboard()
    {
        return view('karyawan.index');
    }
    public function showAdminDashboard()
    {
        return view('admin.admin');
    }

    public function showManajerPage()
    {
        return view('admin.manajer');
    }
}
