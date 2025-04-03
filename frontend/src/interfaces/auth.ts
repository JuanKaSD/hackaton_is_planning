export interface LoginCredentials {
    email: string;
    password: string;
}

export interface SignupCredentials extends LoginCredentials {
    name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
    user_type: 'client' | 'enterprise';
}

export interface AuthResponse {
    token: string;
    user: {
        id: string;
        name: string;
        email: string;
        phone: string;
    };
}

export interface User {
    id?: number;
    name: string;
    email: string;
    phone: string;
    password?: string;
    confirmPassword?: string;
    user_type: 'client' | 'enterprise'; 
    created_at?: string;
    updated_at?: string;
}
