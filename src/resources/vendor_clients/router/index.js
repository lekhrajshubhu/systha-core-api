// Protected routes (requires auth)
const customerProtectedRoutes = [
  {
    path: "dashboard",
    name: "vendorClientDashboardPage",
    component: () => import("../pages/dashboard/DashboardPage.vue"),
    meta: { pageTitle: "Dashboard Overview", auth: true },
  },
  {
    path: "appointments",
    name: "vendorClientAppointmentPage",
    component: () => import("../pages/appointments/AppointmentPage.vue"),
    meta: { pageTitle: "Appointments", auth: true },
  },
  {
    path: "appointments/:id",
    name: "vendorClientAppointmentDetailPage",
    component: () => import("../pages/appointments/AppointmentDetailPage.vue"),
    meta: { pageTitle: "Appointment Detail", auth: true },
  },
  {
    path: "subscriptions",
    name: "vendorClientSubscriptionPage",
    component: () => import("../pages/subscriptions/SubscriptionPage.vue"),
    meta: { pageTitle: "Subscriptions", auth: true },
  },
  {
    path: "subscriptions/:id",
    name: "vendorClientSubscriptionDetailPage",
    component: () => import("../pages/subscriptions/SubscriptionDetailPage.vue"),
    meta: { pageTitle: "Subscription Detail Page", auth: true },
  },
  {
    path: "invoices",
    name: "vendorClientInvoicePage",
    component: () => import("../pages/invoices/InvoicePage.vue"),
    meta: { pageTitle: "Invoices", auth: true },
  },
  {
    path: "payments",
    name: "vendorClientPaymentPage",
    component: () => import("../pages/payments/PaymentPage.vue"),
    meta: { pageTitle: "Payments", auth: true },
  },
  {
    path: "inquiries",
    name: "vendorClientInquiryPage",
    component: () => import("../pages/inquiries/InquiryPage.vue"),
    meta: { pageTitle: "Inquiries", auth: true },
  },
  {
    path: "inquiries/:id",
    name: "vendorClientInquiryDetailPage",
    component: () => import("../pages/inquiries/InquiryDetailPage.vue"),
    meta: { pageTitle: "Inquiry Detail", auth: true },
  },
  {
    path: "quotations/:id",
    name: "vendorClientQuotationDetailPage",
    component: () => import("../pages/inquiries/QuotationDetailPage.vue"),
    meta: { pageTitle: "Quotation Detail", auth: true },
  },
  {
    path: "messages",
    name: "vendorClientMessagePage",
    component: () => import("../pages/messages/MessagePage.vue"),
    meta: { pageTitle: "Messages", auth: true },
  },
  {
    path: "email-notifications",
    name: "vendorClientEmailNotificationPage",
    component: () => import("../pages/notifications/EmailNotificationPage.vue"),
    meta: { pageTitle: "Email Notifications", auth: true },
  },
  {
    path: "reviews",
    name: "vendorClientReviewPage",
    component: () => import("../pages/reviews/ReviewPage.vue"),
    meta: { pageTitle: "Reviews & Feedback", auth: true },
  },
  {
    path: "notifications",
    name: "vendorClientNotificationPage",
    component: () => import("../pages/notifications/NotificationPage.vue"),
    meta: { pageTitle: "Notifications", auth: true },
  },
  {
    path: "profile",
    name: "vendorClientProfilePage",
    component: () => import("../pages/profile/ProfilePage.vue"),
    meta: { pageTitle: "Profile", auth: true },
  },
  {
    path: "settings",
    name: "vendorClientSettingPage",
    component: () => import("../pages/settings/SettingPage.vue"),
    meta: { pageTitle: "Settings", auth: true },
  },
  {
    path: "cards",
    name: "vendorClientCarsPage",
    component: () => import("../pages/cards/CardPage.vue"),
    meta: { pageTitle: "cards", auth: true },
  },
];

const routes = [
  {
    path: "/vendor-clients",
    component: () => import("../layouts/AuthLayout.vue"),
    children: [
      {
        path: "login",
        name: "vendorClientLoginPage",
        component: () => import("../pages/auth/LoginPage.vue"),
      },
      {
        path: "reset-password",
        name: "vendorClientResetPasswordPage",
        component: () => import("../pages/auth/PasswordResetPage.vue"),
      },
    ],
  },
  {
    path: "/vendor-clients",
    component: () => import("../layouts/VendorClientLayout.vue"),
    redirect: (to) => ({ name: "vendorClientDashboardPage", query: to.query }),
    children: customerProtectedRoutes,
  },
];

const registerGuards = (router) => {
  router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem("token");
    const hasToQuery = Object.keys(to.query || {}).length > 0;

    const query = hasToQuery ? to.query : (from.query || {});

    if (to.meta.auth && !isAuthenticated) {
      return next({ name: "vendorClientLoginPage", query });
    }

    if (to.name === "vendorClientLoginPage" && isAuthenticated) {
      return next({ name: "vendorClientDashboardPage", query });
    }

    next();
  });
};

export { routes, registerGuards };
