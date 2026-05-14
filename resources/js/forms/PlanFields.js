export const fields = [
  { key: "owner_type", label: "Owner Type", required: 1, placeholder: "Enter Owner Type", type: "text", isString: false },
  { key: "owner_id", label: "Owner Id", required: 1, placeholder: "Enter Owner Id", type: "number", isString: false },
  { key: "name", label: "Name", required: 1, placeholder: "Enter Name", type: "text", isString: false },
  { key: "desc", label: "Desc", required: 1, placeholder: "Enter Desc", type: "textarea", isString: false },
  { key: "price", label: "Price", required: 1, placeholder: "Enter Price", type: "number", isString: false },
  { key: "duration_value", label: "Duration Value", required: 1, placeholder: "Enter Duration Value", type: "text", isString: false },
  { key: "duration_unit", label: "Duration Unit", required: 1, placeholder: "Enter Duration Unit", type: "select", isString: false,
      options: [
    {
        "value": "days",
        "label": "Days"
    },
    {
        "value": "months",
        "label": "Months"
    },
    {
        "value": "years",
        "label": "Years"
    }
] },
  { key: "status", label: "Status", required: 1, placeholder: "Enter Status", type: "select", isString: false,
      options: [
    {
        "value": "active",
        "label": "Active"
    },
    {
        "value": "draft",
        "label": "Draft"
    }
] }
];