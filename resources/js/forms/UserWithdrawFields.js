export const fields = [
  { key: "user_id", label: "User Id", required: 1, placeholder: "Enter User Id", type: "number", isString: false },
  { key: "user_payment_info_id", label: "User Payment Info Id", required: 1, placeholder: "Enter User Payment Info Id", type: "number", isString: false },
  { key: "amount", label: "Amount", required: 1, placeholder: "Enter Amount", type: "number", isString: false },
  { key: "status", label: "Status", required: 1, placeholder: "Enter Status", type: "select", isString: false,
      options: [
    {
        "value": "pending",
        "label": "Pending"
    },
    {
        "value": "rejected",
        "label": "Rejected"
    },
    {
        "value": "approved",
        "label": "Approved"
    }
] },
  { key: "admin_note", label: "Admin Note", required: 1, placeholder: "Enter Admin Note", type: "textarea", isString: false },
  { key: "admin_id", label: "Admin Id", required: 1, placeholder: "Enter Admin Id", type: "textarea", isString: false },
  { key: "transaction_id", label: "Transaction Id", required: 1, placeholder: "Enter Transaction Id", type: "text", isString: false }
];