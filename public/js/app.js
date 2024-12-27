// API Base URL
const apiUrl = "http://localhost:8080/contacts";

// DOM Elements
const contactList = document.getElementById("contactList");
const addContactForm = document.getElementById("addContactForm");
const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const phoneInput = document.getElementById("phone");

// Modal Elements
const editModal = document.getElementById("editModal");
const editContactForm = document.getElementById("editContactForm");
const editContactId = document.getElementById("editContactId");
const editName = document.getElementById("editName");
const editEmail = document.getElementById("editEmail");
const editPhone = document.getElementById("editPhone");
const closeModalBtn = document.querySelector(".close-btn");

// Fetch all contacts
const fetchContacts = async () => {
  const response = await fetch(apiUrl);
  if (response.ok) {
    const contacts = await response.json();
    renderContacts(contacts);
  } else {
    alert("Failed to fetch contacts!");
  }
};

// Render contacts
const renderContacts = (contacts) => {
  contactList.innerHTML = ""; // Clear the list
  contacts.forEach(contact => {
    const li = document.createElement("li");
    li.innerHTML = `
      <span>${contact.name} - ${contact.email} - ${contact.phone}</span>
      <button class="edit-btn" onclick="editContact('${contact.id}')">Edit</button>
      <button class="delete-btn" onclick="deleteContact('${contact.id}')">Delete</button>
    `;
    contactList.appendChild(li);
  });
};

// Add a new contact
addContactForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const name = nameInput.value.trim();
  const email = emailInput.value.trim();
  const phone = phoneInput.value.trim();

  if (!name || !email || !phone) {
    alert("All fields are required!");
    return;
  }

  const response = await fetch(apiUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ name, email, phone }),
  });

  if (response.ok) {
    fetchContacts(); // Refresh the list
    addContactForm.reset(); // Clear the form
  } else {
    alert("Failed to add contact!");
  }
});

// Delete a contact
const deleteContact = async (id) => {
  const confirmDelete = confirm("Are you sure you want to delete this contact?");
  if (!confirmDelete) return;

  const response = await fetch(`${apiUrl}/${id}`, {
    method: "DELETE",
  });

  if (response.ok) {
    fetchContacts(); // Refresh the list
  } else {
    alert("Failed to delete contact!");
  }
};

// Open modal to edit a contact
const editContact = async (id) => {
  const response = await fetch(`${apiUrl}/${id}`);
  if (response.ok) {
    const contact = await response.json();
    openModal(contact);
  } else {
    alert("Failed to fetch contact details!");
  }
};

// Open the modal and prefill the form
const openModal = (contact) => {
  editContactId.value = contact.id;
  editName.value = contact.name;
  editEmail.value = contact.email;
  editPhone.value = contact.phone;
  editModal.style.display = "block";
};

// Close the modal
const closeModal = () => {
  editModal.style.display = "none";
};

// Event listener for modal close button
closeModalBtn.addEventListener("click", closeModal);

// Close modal when clicking outside the modal content
window.addEventListener("click", (event) => {
  if (event.target === editModal) {
    closeModal();
  }
});

// Update a contact
editContactForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const id = editContactId.value;
  const name = editName.value.trim();
  const email = editEmail.value.trim();
  const phone = editPhone.value.trim();

  if (!name || !email || !phone) {
    alert("All fields are required!");
    return;
  }

  const response = await fetch(`${apiUrl}/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ name, email, phone }),
  });

  if (response.ok) {
    fetchContacts(); // Refresh the list
    closeModal(); // Close the modal
  } else {
    alert("Failed to update contact!");
  }
});

// Fetch and render contacts on page load
fetchContacts();
