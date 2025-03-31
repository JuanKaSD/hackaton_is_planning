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
