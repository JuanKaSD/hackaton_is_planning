<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TokenAuthTestFixture
{
    /**
     * Reset the authentication state and token cache
     */
    public static function resetAuthState()
    {
        // Clean up any active tokens
        DB::table('personal_access_tokens')->truncate();
        
        // Reset authentication state
        Auth::logout();
    }
}
