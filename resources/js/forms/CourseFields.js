export const fields = [
  { key: "title", label: "Title", required: 1, placeholder: "Enter Title", type: "text", isString: false },
  { key: "user_id", label: "User Id", required: 1, placeholder: "Enter User Id", type: "number", isString: false },
  { key: "type", label: "Type", required: 1, placeholder: "Enter Type", type: "select", isString: false,
      options: [
    {
        "value": "recorded",
        "label": "Recorded"
    },
    {
        "value": "online",
        "label": "Online"
    },
    {
        "value": "physical",
        "label": "Physical"
    }
] },
  { key: "category_id", label: "Category Id", required: 1, placeholder: "Enter Category Id", type: "number", isString: false },
  { key: "description", label: "Description", required: 1, placeholder: "Enter Description", type: "textarea", isString: false },
  { key: "image", label: "Image", required: 1, placeholder: "Enter Image", type: "image", isString: true },
  { key: "price_type", label: "Price Type", required: 1, placeholder: "Enter Price Type", type: "select", isString: false,
      options: [
    {
        "value": "free",
        "label": "Free"
    },
    {
        "value": "paid",
        "label": "Paid"
    }
] },
  { key: "price", label: "Price", required: 1, placeholder: "Enter Price", type: "number", isString: false },
  { key: "final_price", label: "Final Price", required: 1, placeholder: "Enter Final Price", type: "number", isString: false },
  { key: "status", label: "Status", required: 1, placeholder: "Enter Status", type: "select", isString: false,
      options: [
    {
        "value": "published",
        "label": "Published"
    },
    {
        "value": "draft",
        "label": "Draft"
    }
] }
];