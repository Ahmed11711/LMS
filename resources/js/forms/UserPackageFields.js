export const fields = [
  { key: "user_id", label: "User Id", required: 1, placeholder: "Enter User Id", type: "number", isString: false },
  { key: "package_id", label: "Package Id", required: 1, placeholder: "Enter Package Id", type: "number", isString: false },
  { key: "start_date", label: "Start Date", required: 1, placeholder: "Enter Start Date", type: "text", isString: false },
  { key: "end_date", label: "End Date", required: 1, placeholder: "Enter End Date", type: "text", isString: false },
  { key: "active", label: "Active", required: 1, placeholder: "Enter Active", type: "boolean", isString: false },
  { key: "transaction_id", label: "Transaction Id", required: 1, placeholder: "Enter Transaction Id", type: "text", isString: false },
  { key: "status", label: "Status", required: 1, placeholder: "Enter Status", type: "select", isString: false,
      options: [
    {
        "value": "pending",
        "label": "Pending"
    },
    {
        "value": "active",
        "label": "Active"
    },
    {
        "value": "expired",
        "label": "Expired"
    },
    {
        "value": "cancelled",
        "label": "Cancelled"
    }
] },
  { key: "price", label: "Price", required: 1, placeholder: "Enter Price", type: "number", isString: false }
];