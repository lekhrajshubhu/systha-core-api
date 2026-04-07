// Protected routes (requires auth)
const accountProtectedRoutes = [
  {
    path: "dashboard",
    name: "globalDashboardPage",
    component: () => import("../pages/dashboard/DashboardPage.vue"),
    meta: { pageTitle: "Dashboard Overview", auth: true },
  },
  {
    path: "appointments",
    name: "globalAppointmentPage",
    component: () => import("../pages/appointments/AppointmentPage.vue"),
    meta: { pageTitle: "Appointments", auth: true },
  },
  {
    path: "appointments/:id",
    name: "globalAppointmentDetailPage",
    component: () => import("../pages/appointments/AppointmentDetailPage.vue"),
    meta: { pageTitle: "Appointment Detail", auth: true },
  },
  {
    path: "subscriptions",
    name: "globalSubscriptionPage",
    component: () => import("../pages/subscriptions/SubscriptionPage.vue"),
    meta: { pageTitle: "Subscriptions", auth: true },
  },
  {
    path: "subscriptions/:id",
    name: "globalSubscriptionDetailPage",
    component: () => import("../pages/subscriptions/SubscriptionDetailPage.vue"),
    meta: { pageTitle: "Subscription Detail Page", auth: true },
  },
  {
    path: "invoices",
    name: "globalInvoicePage",
    component: () => import("../pages/invoices/InvoicePage.vue"),
    meta: { pageTitle: "Invoices", auth: true },
  },
  {
    path: "payments",
    name: "globalPaymentPage",
    component: () => import("../pages/payments/PaymentPage.vue"),
    meta: { pageTitle: "Payments", auth: true },
  },
  {
    path: "inquiries",
    name: "globalInquiryPage",
    component: () => import("../pages/inquiries/InquiryPage.vue"),
    meta: { pageTitle: "Inquiries", auth: true },
  },
  {
    path: "inquiries/:id",
    name: "globalInquiryDetailPage",
    component: () => import("../pages/inquiries/InquiryDetailPage.vue"),
    meta: { pageTitle: "Inquiry Detail", auth: true },
  },
  {
    path: "quotations/:id",
    name: "globalQuotationDetailPage",
    component: () => import("../pages/inquiries/QuotationDetailPage.vue"),
    meta: { pageTitle: "Quotation Detail", auth: true },
  },
  {
    path: "messages",
    name: "globalMessagePage",
    component: () => import("../pages/messages/MessagePage.vue"),
    meta: { pageTitle: "Messages", auth: true },
  },
  {
    path: "email-notifications",
    name: "globalEmailNotificationPage",
    component: () => import("../pages/notifications/EmailNotificationPage.vue"),
    meta: { pageTitle: "Email Notifications", auth: true },
  },
  {
    path: "reviews",
    name: "globalReviewPage",
    component: () => import("../pages/reviews/ReviewPage.vue"),
    meta: { pageTitle: "Reviews & Feedback", auth: true },
  },
  {
    path: "notifications",
    name: "globalNotificationPage",
    component: () => import("../pages/notifications/NotificationPage.vue"),
    meta: { pageTitle: "Notifications", auth: true },
  },
  {
    path: "profile",
    name: "globalProfilePage",
    component: () => import("../pages/profile/ProfilePage.vue"),
    meta: { pageTitle: "Profile", auth: true },
  },
  {
    path: "settings",
    name: "globalSettingPage",
    component: () => import("../pages/settings/SettingPage.vue"),
    meta: { pageTitle: "Settings", auth: true },
  },
  {
    path: "cards",
    name: "globalCarsPage",
    component: () => import("../pages/cards/CardPage.vue"),
    meta: { pageTitle: "cards", auth: true },
  },
];

const routes = [
  {
    path: "/global-clients",
    component: () => import("../layouts/GlobalClientLayout.vue"),
    redirect: { name: "globalDashboardPage" },
    children: accountProtectedRoutes,
  },
  {
    path: "/global-clients",
    component: () => import("../layouts/AuthLayout.vue"),
    children: [
      {
        path: "login",
        name: "globalLoginPage",
        component: () => import("../pages/auth/LoginPage.vue"),
      },
      {
        path: "reset-password",
        name: "globalResetPasswordPage",
        component: () => import("../pages/auth/PasswordResetPage.vue"),
      },
    ],
  },
];

const registerGuards = (router) => {
  router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem("token");

    if (to.meta.auth && !isAuthenticated) {
      return next({ name: "globalLoginPage" });
    }

    if (to.name === "globalLoginPage" && isAuthenticated) {
      return next({ name: "globalDashboardPage" });
    }

    next();
  });
};

export { routes, registerGuards };
