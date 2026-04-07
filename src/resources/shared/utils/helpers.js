// src/utils/helpers.js

export function formatDate(dateStr) {
  const date = new Date(dateStr);
  const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-based
  const day = String(date.getDate()).padStart(2, "0");
  const year = date.getFullYear();
  return `${month}/${day}/${year}`;
}
export function formatTime(timeStr) {
  const date = new Date(`1970-01-01T${timeStr}`);
  return date.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

export function formatDateTime(dateStr) {
  const date = new Date(dateStr);
  const options = {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  };
  return date.toLocaleString("en-US", options);
}
export function getStatusColor(status) {
  switch (status?.toLowerCase()) {
    case "active":
      return "green";
    case "cancelled":
    case "expired":
      return "red";
    case "pending":
      return "orange";
    default:
      return "blue";
  }
}
export function getInquiryStatusColor(status) {
  switch (status?.toLowerCase()) {
    case "new":
      return "warning";
    case "quoted":
      return "info";
    case "success":
    case "active":
    case "accepted":
    case "confirmed":
      return "green";
    case "converted":
      return "teal";
    case "failed":
    case "cancelled":
      return "red";
    default:
      return "orange"; // fallback color for unknown/pending statuses
  }
}

export function formatAmount(amount, locale = "en-US", currency = "USD") {
  if (isNaN(amount)) return "0.00";

  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
}

export function formatTimeAgo(dateStr) {
  const now = new Date();
  const date = new Date(dateStr);
  const diffInSeconds = Math.floor((now - date) / 1000);

  const rtf = new Intl.RelativeTimeFormat("en", { numeric: "auto" });

  const ranges = {
    year: 31536000,
    month: 2592000,
    week: 604800,
    day: 86400,
    hour: 3600,
    minute: 60,
    second: 1,
  };

  for (const [unit, secondsInUnit] of Object.entries(ranges)) {
    const delta = Math.floor(diffInSeconds / secondsInUnit);
    if (delta >= 1) {
      return rtf.format(-delta, unit); // negative for past tense
    }
  }

  return "just now";
}

// helpers.js

/**
 * Format phone number in (XXX) XXX-XXXX format if 10 digits,
 * otherwise return as is or with basic spacing.
 *
 * @param {string | number} phone
 * @returns {string}
 */
export function formatPhoneNumber(phone) {
  if (!phone) return "";

  // Convert to string and remove all non-digits
  const digits = phone.toString().replace(/\D/g, "");

  if (digits.length === 10) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
  }

  // For 7-digit numbers (local)
  if (digits.length === 7) {
    return `${digits.slice(0, 3)}-${digits.slice(3)}`;
  }

  // For other lengths, you can format differently or just return digits spaced every few digits
  if (digits.length > 10) {
    // Example: international number, format with spaces every 3-4 digits
    return digits.replace(/(\d{3})(?=\d)/g, "$1 ");
  }

  // Return digits as is if no better format applies
  return digits;
}
