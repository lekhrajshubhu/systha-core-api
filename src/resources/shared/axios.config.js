import axios from "axios";

// Manually define base URLs
const DEV_BASE_URL = "https://dev-cleaning.test/api/v1";
const PROD_BASE_URL = `${window.location.origin}/api/v1`;
const BASE_URL = window.location.origin.includes("dev-cleaning.test")
  ? DEV_BASE_URL
  : PROD_BASE_URL;

const API_PREFIX = window.location.pathname.startsWith("/global-clients")
  ? "global-clients"
  : "vendor-clients";

const $axios = axios.create({
  baseURL: `${BASE_URL}/${API_PREFIX}`,
  timeout: 10000,
});

// Attach token to requests if available
$axios.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  const vendorCode = localStorage.getItem("vendor_code");
  if (vendorCode) {
    config.headers["Vendor-Code"] = vendorCode;
  }
  return config;
});

// Global 401 handling
$axios.interceptors.response.use(
  (res) => res.data, // ✅ now always returns only `data`
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem("token");
      // window.location.href = "/customers/login";
    }
    return Promise.reject(err);
  }
);

export default $axios;
