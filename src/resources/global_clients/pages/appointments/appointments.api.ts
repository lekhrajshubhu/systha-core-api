import $axios from "@shared/axios.config";

export type AppointmentItem = {
  id: string;
  appointmentId: string;
  title: string;
  vendor: string;
  time: string;
  status: string;
  isPaid: boolean;
  icon: string;
  iconTone: string;
};

type ListResponse = {
  data: AppointmentItem[];
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
  } | null;
};

const ICON_MAP: Record<string, { icon: string; iconTone: string }> = {
  booked: { icon: "mdi-calendar-check", iconTone: "appointment-icon--peach" },
  pending: { icon: "mdi-timer-sand", iconTone: "appointment-icon--amber" },
  completed: { icon: "mdi-check-decagram", iconTone: "appointment-icon--green" },
  cancelled: { icon: "mdi-close-circle", iconTone: "appointment-icon--red" },
};

const toTitleCase = (value: string) =>
  value
    .split("_")
    .join(" ")
    .split(" ")
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");

const formatDateTime = (dateValue: string | null, timeValue: string | null): string => {
  if (!dateValue) {
    return "No date";
  }

  const now = new Date();
  const inputDate = new Date(dateValue);
  const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const startOfInput = new Date(inputDate.getFullYear(), inputDate.getMonth(), inputDate.getDate());
  const dayDiff = Math.round((startOfInput.getTime() - startOfToday.getTime()) / 86400000);

  const dateLabel =
    dayDiff === 0
      ? "Today"
      : dayDiff === 1
      ? "Tomorrow"
      : inputDate.toLocaleDateString("en-US", {
          month: "short",
          day: "numeric",
          year: "numeric",
        });

  if (!timeValue) {
    return dateLabel;
  }

  const asDate = new Date(`1970-01-01T${timeValue}`);
  const timeLabel = Number.isNaN(asDate.getTime())
    ? timeValue
    : asDate.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit" });

  return `${dateLabel}, ${timeLabel}`;
};

const mapAppointmentItem = (item: any): AppointmentItem => {
  const statusKey = String(item?.status || "pending").toLowerCase();
  const iconMeta = ICON_MAP[statusKey] || ICON_MAP.pending;
  const appointmentNo = String(item?.appointment_no || item?.id || "");
  const fallbackTitle = appointmentNo ? `Appointment ${appointmentNo}` : "Appointment";

  return {
    id: appointmentNo || `apt-${String(item?.id || "")}`,
    appointmentId: String(item?.id || ""),
    title: String(item?.description || "").trim() || fallbackTitle,
    vendor: String(item?.vendor?.name || "Unknown Vendor"),
    time: formatDateTime(item?.appointment_date || null, item?.appointment_time || null),
    status: toTitleCase(statusKey),
    isPaid: Boolean(Number(item?.is_paid || 0)),
    icon: iconMeta.icon,
    iconTone: iconMeta.iconTone,
  };
};

export const appointmentsApi = {
  async list(params: Record<string, unknown> = {}): Promise<ListResponse> {
    const resp = await $axios.get("/appointments", { params });
    const items = Array.isArray(resp?.data) ? resp.data : [];

    return {
      data: items.map(mapAppointmentItem),
      meta: resp?.meta || null,
    };
  },

  async detail(id: string | number) {
    return $axios.get(`/appointments/${id}`);
  },
};

