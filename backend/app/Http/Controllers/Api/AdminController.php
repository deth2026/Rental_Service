<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Get admin dashboard stats
     */
    public function stats(Request $request)
    {
        // Get total number of shops
        $totalShops = Shop::count();
        
        // Get average rating from feedback
        $averageRating = Feedback::avg('rating');
        
        // Round to 1 decimal place
        $averageRating = $averageRating ? round($averageRating, 1) : 0;
        
        // Get user counts by role
        $adminCount = User::where('role', 'admin')->count();
        $shopOwnerCount = User::where('role', 'shop_owner')->count();
        $customerCount = User::where('role', 'customer')->count();
        
        // Get current user's last login time
        $user = $request->user();
        $lastLogin = ($user && User::hasLastLoginColumn()) ? $user->last_login : null;
        
        return response()->json([
            'total_shops' => $totalShops,
            'average_rating' => $averageRating,
            'admin_count' => $adminCount,
            'shop_owner_count' => $shopOwnerCount,
            'customer_count' => $customerCount,
            'last_login' => $lastLogin,
        ]);
    }
}
