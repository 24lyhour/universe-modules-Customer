// Customer Module Types

export interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    date_of_birth: string | null;
    age: number | null;
    gender: 'male' | 'female' | 'other' | null;
    status: 'active' | 'inactive' | 'suspended';
    avatar: string | null;
    email_verified: boolean;
    email_verified_at: string | null;
    phone_verified: boolean;
    phone_verified_at: string | null;
    two_factor_enabled: boolean;
    referral_code: string | null;
    outlet: CustomerOutlet | null;
    wallet: CustomerWallet | null;
    referrer: CustomerReferrer | null;
    referrals: CustomerReferral[];
    created_at: string;
    updated_at: string;
}

export interface CustomerOutlet {
    id: number;
    name: string | null;
}

export interface CustomerWallet {
    id: number;
    balance: number;
    currency?: string;
}

export interface CustomerReferrer {
    id: number;
    name: string;
}

export interface CustomerReferral {
    id: number;
    name: string;
    email: string;
    status: string;
}

export interface CustomerStats {
    total: number;
    active: number;
    inactive: number;
    suspended: number;
    verified: number;
    two_factor_enabled: number;
    new_this_month?: number;
    new_this_week?: number;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    path?: string;
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
    links?: PaginationLinks;
}

export interface CustomerFilters {
    status?: string;
    search?: string;
    verified?: boolean;
    gender?: string;
    from_date?: string;
    to_date?: string;
}

// Form Data Types
export interface CustomerFormData {
    name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
    address: string;
    date_of_birth: string;
    gender: string;
    status: string;
    avatar?: File | null;
}

export interface CustomerUpdateFormData {
    name?: string;
    email?: string;
    phone?: string;
    address?: string;
    date_of_birth?: string;
    gender?: string;
    status?: string;
    avatar?: File | null;
}

// Page Props Types
export interface CustomerIndexProps {
    customers: PaginatedResponse<Customer>;
    filters: CustomerFilters;
    stats: CustomerStats;
}

export interface CustomerShowProps {
    customer: Customer;
}

export interface CustomerCreateProps {
    // Add any props needed for create page
}

export interface CustomerEditProps {
    customer: Customer;
}
