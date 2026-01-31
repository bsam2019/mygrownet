import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token for all axios requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
} else {
    console.error('CSRF token not found');
}

// Add axios interceptor to refresh CSRF token before each request
// This prevents 419 errors when the token expires or changes
window.axios.interceptors.request.use(function (config) {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

// Add axios interceptor to handle 419 CSRF errors
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            console.warn('[Axios] CSRF token mismatch (419) - reloading page');
            // Reload the page to get a fresh CSRF token
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
