export const fields = [
  { key: "academy_id", label: "Academy Id", required: 1, placeholder: "Enter Academy Id", type: "number", isString: false },
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
  { key: "approved_by", label: "Approved By", required: 1, placeholder: "Enter Approved By", type: "number", isString: false },
  { key: "payment_method", label: "Payment Method", required: 1, placeholder: "Enter Payment Method", type: "text", isString: false },
  { key: "payment_details", label: "Payment Details", required: 1, placeholder: "Enter Payment Details", type: "text", isString: false },
  { key: "receipt_image", label: "Receipt Image", required: 1, placeholder: "Enter Receipt Image", type: "image", isString: true },
  { key: "transaction_number", label: "Transaction Number", required: 1, placeholder: "Enter Transaction Number", type: "text", isString: false },
  { key: "transaction_id", label: "Transaction Id", required: 1, placeholder: "Enter Transaction Id", type: "text", isString: false }
];