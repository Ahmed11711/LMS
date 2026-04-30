export const fields = [
  { key: "user_id", label: "User Id", required: 1, placeholder: "Enter User Id", type: "number", isString: false },
  { key: "course_id", label: "Course Id", required: 1, placeholder: "Enter Course Id", type: "number", isString: false },
  { key: "starts_at", label: "Starts At", required: 1, placeholder: "Enter Starts At", type: "text", isString: false },
  { key: "status", label: "Status", required: 1, placeholder: "Enter Status", type: "select", isString: false,
      options: [
    {
        "value": "active",
        "label": "Active"
    },
    {
        "value": "refunded",
        "label": "Refunded"
    },
    {
        "value": "cancelled",
        "label": "Cancelled"
    },
    {
        "value": "pending",
        "label": "Pending"
    }
] }
];