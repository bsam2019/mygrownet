# Frontend Integration Guide

This guide provides comprehensive instructions for integrating with the VBIF Reward System API from frontend applications.

## Getting Started

### Base Configuration

```javascript
// config/api.js
const API_CONFIG = {
  baseURL: process.env.VITE_API_URL || 'https://api.vbif.com',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};

export default API_CONFIG;
```

### API Client Setup

```javascript
// services/apiClient.js
import axios from 'axios';
import API_CONFIG from '../config/api';

class ApiClient {
  constructor() {
    this.client = axios.create(API_CONFIG);
    this.setupInterceptors();
  }

  setupInterceptors() {
    // Request interceptor for auth token
    this.client.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor for error handling
    this.client.interceptors.response.use(
      (response) => response.data,
      (error) => {
        if (error.response?.status === 401) {
          this.handleUnauthorized();
        }
        return Promise.reject(this.formatError(error));
      }
    );
  }

  handleUnauthorized() {
    localStorage.removeItem('auth_token');
    window.location.href = '/login';
  }

  formatError(error) {
    if (error.response?.data) {
      return {
        message: error.response.data.message,
        errors: error.response.data.errors,
        code: error.response.data.code,
        status: error.response.status
      };
    }
    return {
      message: 'Network error occurred',
      status: 0
    };
  }

  // HTTP methods
  get(url, params = {}) {
    return this.client.get(url, { params });
  }

  post(url, data = {}) {
    return this.client.post(url, data);
  }

  put(url, data = {}) {
    return this.client.put(url, data);
  }

  patch(url, data = {}) {
    return this.client.patch(url, data);
  }

  delete(url) {
    return this.client.delete(url);
  }
}

export default new ApiClient();
```

## Authentication Integration

### Login Implementation

```javascript
// services/authService.js
import apiClient from './apiClient';

class AuthService {
  async login(credentials) {
    try {
      const response = await apiClient.post('/login', credentials);
      
      if (response.success) {
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        return response.data;
      }
      
      throw new Error(response.message);
    } catch (error) {
      throw error;
    }
  }

  async register(userData) {
    try {
      const response = await apiClient.post('/register', userData);
      
      if (response.success) {
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        return response.data;
      }
      
      throw new Error(response.message);
    } catch (error) {
      throw error;
    }
  }

  async logout() {
    try {
      await apiClient.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
  }

  getCurrentUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  isAuthenticated() {
    return !!localStorage.getItem('auth_token');
  }
}

export default new AuthService();
```

### React Authentication Hook

```javascript
// hooks/useAuth.js
import { useState, useEffect, createContext, useContext } from 'react';
import authService from '../services/authService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const initAuth = async () => {
      try {
        if (authService.isAuthenticated()) {
          const userData = authService.getCurrentUser();
          setUser(userData);
        }
      } catch (error) {
        console.error('Auth initialization error:', error);
      } finally {
        setLoading(false);
      }
    };

    initAuth();
  }, []);

  const login = async (credentials) => {
    setLoading(true);
    try {
      const data = await authService.login(credentials);
      setUser(data.user);
      return data;
    } finally {
      setLoading(false);
    }
  };

  const register = async (userData) => {
    setLoading(true);
    try {
      const data = await authService.register(userData);
      setUser(data.user);
      return data;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  const value = {
    user,
    login,
    register,
    logout,
    loading,
    isAuthenticated: !!user
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

## Dashboard Integration

### Dashboard Service

```javascript
// services/dashboardService.js
import apiClient from './apiClient';

class DashboardService {
  async getDashboardData() {
    return apiClient.get('/dashboard');
  }

  async getRealTimeEarnings() {
    return apiClient.get('/dashboard/real-time-earnings');
  }

  async getWithdrawalEligibility(params = {}) {
    return apiClient.get('/dashboard/withdrawal-eligibility', params);
  }

  async getPenaltyPreview(params) {
    return apiClient.get('/dashboard/penalty-preview', params);
  }

  async getDashboardMetrics() {
    return apiClient.get('/dashboard/metrics');
  }

  async getInvestmentTrends(params = {}) {
    return apiClient.get('/dashboard/investment-trends', params);
  }

  async getTierUpgradeRecommendations() {
    return apiClient.get('/dashboard/tier-upgrade-recommendations');
  }

  async getMatrixData() {
    return apiClient.get('/dashboard/matrix-data');
  }

  async getNotificationsAndActivity(params = {}) {
    return apiClient.get('/dashboard/notifications-activity', params);
  }
}

export default new DashboardService();
```

### React Dashboard Hook

```javascript
// hooks/useDashboard.js
import { useState, useEffect } from 'react';
import dashboardService from '../services/dashboardService';

export const useDashboard = () => {
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await dashboardService.getDashboardData();
      
      if (response.success) {
        setDashboardData(response.data);
      } else {
        setError(response.message);
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  return {
    dashboardData,
    loading,
    error,
    refetch: fetchDashboardData
  };
};

export const useRealTimeEarnings = (interval = 30000) => {
  const [earnings, setEarnings] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchEarnings = async () => {
      try {
        const response = await dashboardService.getRealTimeEarnings();
        if (response.success) {
          setEarnings(response.data);
        }
      } catch (error) {
        console.error('Error fetching real-time earnings:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchEarnings();
    const intervalId = setInterval(fetchEarnings, interval);

    return () => clearInterval(intervalId);
  }, [interval]);

  return { earnings, loading };
};
```

## Investment Integration

### Investment Service

```javascript
// services/investmentService.js
import apiClient from './apiClient';

class InvestmentService {
  async getInvestments(params = {}) {
    return apiClient.get('/investments', params);
  }

  async createInvestment(investmentData) {
    return apiClient.post('/investments', investmentData);
  }

  async getInvestment(id) {
    return apiClient.get(`/investments/${id}`);
  }

  async getInvestmentHistory(params = {}) {
    return apiClient.get('/investments/history/all', params);
  }

  async getPerformanceMetrics(params = {}) {
    return apiClient.get('/investments/performance/metrics', params);
  }

  async requestTierUpgrade(upgradeData) {
    return apiClient.post('/investments/tier-upgrade', upgradeData);
  }

  async requestWithdrawal(investmentId, withdrawalData) {
    return apiClient.post(`/investments/${investmentId}/withdrawal`, withdrawalData);
  }

  async getOpportunities(params = {}) {
    return apiClient.get('/opportunities', params);
  }

  async getPortfolio() {
    return apiClient.get('/portfolio');
  }
}

export default new InvestmentService();
```

### Investment Form Component (React)

```jsx
// components/InvestmentForm.jsx
import React, { useState, useEffect } from 'react';
import investmentService from '../services/investmentService';
import { useAuth } from '../hooks/useAuth';

const InvestmentForm = ({ onSuccess, onError }) => {
  const { user } = useAuth();
  const [formData, setFormData] = useState({
    tier_id: '',
    amount: '',
    payment_method: 'bank_transfer',
    referrer_code: '',
    payment_proof: null
  });
  const [tiers, setTiers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchTiers();
  }, []);

  const fetchTiers = async () => {
    try {
      // This would typically come from a tiers endpoint
      const response = await investmentService.getOpportunities();
      if (response.success) {
        setTiers(response.data.tiers || []);
      }
    } catch (error) {
      console.error('Error fetching tiers:', error);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, files } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: files ? files[0] : value
    }));
    
    // Clear error for this field
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const formDataToSend = new FormData();
      Object.keys(formData).forEach(key => {
        if (formData[key] !== null && formData[key] !== '') {
          formDataToSend.append(key, formData[key]);
        }
      });

      const response = await investmentService.createInvestment(formDataToSend);
      
      if (response.success) {
        onSuccess?.(response.data);
        setFormData({
          tier_id: '',
          amount: '',
          payment_method: 'bank_transfer',
          referrer_code: '',
          payment_proof: null
        });
      } else {
        setErrors(response.errors || {});
        onError?.(response.message);
      }
    } catch (error) {
      setErrors(error.errors || {});
      onError?.(error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <label className="block text-sm font-medium text-gray-700">
          Investment Tier
        </label>
        <select
          name="tier_id"
          value={formData.tier_id}
          onChange={handleInputChange}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          required
        >
          <option value="">Select a tier</option>
          {tiers.map(tier => (
            <option key={tier.id} value={tier.id}>
              {tier.name} - K{tier.minimum_investment} minimum
            </option>
          ))}
        </select>
        {errors.tier_id && (
          <p className="mt-1 text-sm text-red-600">{errors.tier_id[0]}</p>
        )}
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700">
          Investment Amount (K)
        </label>
        <input
          type="number"
          name="amount"
          value={formData.amount}
          onChange={handleInputChange}
          min="500"
          step="100"
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          required
        />
        {errors.amount && (
          <p className="mt-1 text-sm text-red-600">{errors.amount[0]}</p>
        )}
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700">
          Payment Method
        </label>
        <select
          name="payment_method"
          value={formData.payment_method}
          onChange={handleInputChange}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        >
          <option value="bank_transfer">Bank Transfer</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="cash_deposit">Cash Deposit</option>
        </select>
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700">
          Referrer Code (Optional)
        </label>
        <input
          type="text"
          name="referrer_code"
          value={formData.referrer_code}
          onChange={handleInputChange}
          placeholder="Enter referrer code"
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        />
        {errors.referrer_code && (
          <p className="mt-1 text-sm text-red-600">{errors.referrer_code[0]}</p>
        )}
      </div>

      <div>
        <label className="block text-sm font-medium text-gray-700">
          Payment Proof
        </label>
        <input
          type="file"
          name="payment_proof"
          onChange={handleInputChange}
          accept="image/*,.pdf"
          className="mt-1 block w-full"
          required
        />
        {errors.payment_proof && (
          <p className="mt-1 text-sm text-red-600">{errors.payment_proof[0]}</p>
        )}
      </div>

      <button
        type="submit"
        disabled={loading}
        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
      >
        {loading ? 'Creating Investment...' : 'Create Investment'}
      </button>
    </form>
  );
};

export default InvestmentForm;
```

## Referral Integration

### Referral Service

```javascript
// services/referralService.js
import apiClient from './apiClient';

class ReferralService {
  async getReferralDashboard() {
    return apiClient.get('/referrals');
  }

  async getReferralTree(params = {}) {
    return apiClient.get('/referrals/tree', params);
  }

  async getReferralStatistics() {
    return apiClient.get('/referrals/statistics');
  }

  async getCommissionHistory(params = {}) {
    return apiClient.get('/referrals/commissions', params);
  }

  async generateReferralCode() {
    return apiClient.post('/referrals/generate-code');
  }

  async validateReferralCode(code) {
    return apiClient.post('/referrals/validate-code', { referral_code: code });
  }

  async getMatrixPosition() {
    return apiClient.get('/referrals/matrix-position');
  }

  async getMatrixGenealogy(params = {}) {
    return apiClient.get('/referrals/matrix-genealogy', params);
  }

  async getReferralsByLevel(level) {
    return apiClient.get('/referrals/referrals-by-level', { level });
  }

  async calculateCommission(params) {
    return apiClient.post('/referrals/calculate-commission', params);
  }

  async getPerformanceReport() {
    return apiClient.get('/referrals/performance-report');
  }

  async exportData(format, type) {
    return apiClient.post('/referrals/export', { format, type });
  }
}

export default new ReferralService();
```

## OTP Integration

### OTP Service

```javascript
// services/otpService.js
import apiClient from './apiClient';

class OtpService {
  async generateOtp(purpose, deliveryMethod = 'sms') {
    return apiClient.post('/otp/generate', {
      purpose,
      delivery_method: deliveryMethod
    });
  }

  async verifyOtp(otpCode, purpose) {
    return apiClient.post('/otp/verify', {
      otp_code: otpCode,
      purpose
    });
  }

  async resendOtp(otpId, deliveryMethod) {
    return apiClient.post('/otp/resend', {
      otp_id: otpId,
      delivery_method: deliveryMethod
    });
  }

  async getOtpStatus(purpose) {
    return apiClient.get('/otp/status', { purpose });
  }
}

export default new OtpService();
```

### OTP Component (React)

```jsx
// components/OtpVerification.jsx
import React, { useState, useEffect } from 'react';
import otpService from '../services/otpService';

const OtpVerification = ({ purpose, onVerified, onError }) => {
  const [otpCode, setOtpCode] = useState('');
  const [otpId, setOtpId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [timeLeft, setTimeLeft] = useState(0);
  const [canResend, setCanResend] = useState(false);

  useEffect(() => {
    generateOtp();
  }, []);

  useEffect(() => {
    if (timeLeft > 0) {
      const timer = setTimeout(() => setTimeLeft(timeLeft - 1), 1000);
      return () => clearTimeout(timer);
    } else {
      setCanResend(true);
    }
  }, [timeLeft]);

  const generateOtp = async () => {
    try {
      setLoading(true);
      const response = await otpService.generateOtp(purpose);
      
      if (response.success) {
        setOtpId(response.data.otp_id);
        const expiresAt = new Date(response.data.expires_at);
        const now = new Date();
        setTimeLeft(Math.max(0, Math.floor((expiresAt - now) / 1000)));
        setCanResend(false);
      }
    } catch (error) {
      onError?.(error.message);
    } finally {
      setLoading(false);
    }
  };

  const verifyOtp = async () => {
    if (!otpCode || otpCode.length !== 6) {
      onError?.('Please enter a valid 6-digit code');
      return;
    }

    try {
      setLoading(true);
      const response = await otpService.verifyOtp(otpCode, purpose);
      
      if (response.success && response.data.verified) {
        onVerified?.(response.data);
      } else {
        onError?.('Invalid OTP code');
      }
    } catch (error) {
      onError?.(error.message);
    } finally {
      setLoading(false);
    }
  };

  const resendOtp = async () => {
    if (!canResend || !otpId) return;

    try {
      setLoading(true);
      const response = await otpService.resendOtp(otpId, 'sms');
      
      if (response.success) {
        const expiresAt = new Date(response.data.expires_at);
        const now = new Date();
        setTimeLeft(Math.max(0, Math.floor((expiresAt - now) / 1000)));
        setCanResend(false);
        setOtpCode('');
      }
    } catch (error) {
      onError?.(error.message);
    } finally {
      setLoading(false);
    }
  };

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div className="space-y-4">
      <div>
        <label className="block text-sm font-medium text-gray-700">
          Enter 6-digit verification code
        </label>
        <input
          type="text"
          value={otpCode}
          onChange={(e) => setOtpCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
          placeholder="000000"
          className="mt-1 block w-full text-center text-2xl tracking-widest rounded-md border-gray-300 shadow-sm"
          maxLength={6}
        />
      </div>

      <div className="flex justify-between items-center">
        <div className="text-sm text-gray-500">
          {timeLeft > 0 ? (
            `Code expires in ${formatTime(timeLeft)}`
          ) : (
            'Code has expired'
          )}
        </div>
        
        <button
          type="button"
          onClick={resendOtp}
          disabled={!canResend || loading}
          className="text-sm text-blue-600 hover:text-blue-500 disabled:text-gray-400"
        >
          Resend Code
        </button>
      </div>

      <button
        type="button"
        onClick={verifyOtp}
        disabled={loading || otpCode.length !== 6}
        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
      >
        {loading ? 'Verifying...' : 'Verify Code'}
      </button>
    </div>
  );
};

export default OtpVerification;
```

## Error Handling

### Global Error Handler

```javascript
// utils/errorHandler.js
export const handleApiError = (error, showToast = true) => {
  let message = 'An unexpected error occurred';
  let details = {};

  if (error.response?.data) {
    message = error.response.data.message || message;
    details = error.response.data.errors || {};
  } else if (error.message) {
    message = error.message;
  }

  if (showToast) {
    // Assuming you have a toast notification system
    toast.error(message);
  }

  return { message, details };
};

export const formatValidationErrors = (errors) => {
  const formatted = {};
  
  Object.keys(errors).forEach(field => {
    formatted[field] = Array.isArray(errors[field]) 
      ? errors[field][0] 
      : errors[field];
  });

  return formatted;
};
```

## Best Practices

### 1. Token Management
- Store tokens securely (consider httpOnly cookies for web apps)
- Implement automatic token refresh
- Handle token expiration gracefully

### 2. Error Handling
- Implement global error handling
- Show user-friendly error messages
- Log errors for debugging

### 3. Loading States
- Show loading indicators for async operations
- Disable forms during submission
- Provide feedback for long-running operations

### 4. Data Caching
- Cache frequently accessed data
- Implement cache invalidation strategies
- Use optimistic updates where appropriate

### 5. Security
- Validate all user inputs
- Sanitize data before display
- Implement proper CSRF protection
- Use HTTPS in production

### 6. Performance
- Implement pagination for large datasets
- Use debouncing for search inputs
- Optimize API calls with proper caching
- Implement lazy loading where appropriate

This integration guide provides a solid foundation for building frontend applications that interact with the VBIF Reward System API. Adapt the examples to your specific frontend framework and requirements.