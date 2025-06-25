@extends('layouts.app')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">User Management</h1>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <!-- User Search and Filter -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 space-y-3 sm:space-y-0">
                <div class="relative">
                    <input type="text" placeholder="Search users..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <select class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option>All Roles</option>
                        <option>Admin</option>
                        <option>User</option>
                        <option>Viewer</option>
                    </select>
                    
                    <button id="addUserBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-plus mr-2"></i>
                        Add User
                    </button>
                </div>
            </div>
            
            <!-- Add User Modal -->
            <div id="addUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900">Add New User</h3>
                        <div class="mt-2 px-7 py-3">
                            <form id="addUserForm">
                                <input type="hidden" id="editUserId" name="editUserId">
                                <div class="mb-4">
                                    <label for="userName" class="block text-sm font-medium text-gray-700 text-left">Name</label>
                                    <input type="text" id="userName" name="userName" required 
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="mb-4">
                                    <label for="userEmail" class="block text-sm font-medium text-gray-700 text-left">Email</label>
                                    <input type="email" id="userEmail" name="userEmail" required 
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="mb-4">
                                    <label for="userRole" class="block text-sm font-medium text-gray-700 text-left">Role</label>
                                    <select id="userRole" name="userRole" required 
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                </div>
                                <div class="flex justify-end mt-6 space-x-3">
                                    <button type="button" id="closeModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit" id="submitButton" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Add User
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <svg class="mx-auto flex-shrink-0 w-12 h-12 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Delete User</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this user? This action cannot be undone.
                            </p>
                            <div class="flex justify-end mt-6 space-x-3">
                                <button type="button" id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                    Cancel
                                </button>
                                <button type="button" id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Active
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Demo User 1 -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium">IT</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Infrastructure Team</div>
                                        <div class="text-sm text-gray-500">serverpulseinfrastructure@tourism.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    User
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Just now
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        
                        <!-- Demo User 2 -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium">AT</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Admin Team</div>
                                        <div class="text-sm text-gray-500">serverpulseadmin@tourism.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Admin
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                3 hours ago
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        
                        <!-- Demo User 3 -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <span class="text-green-600 font-medium">AO</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Application Owner</div>
                                        <div class="text-sm text-gray-500">serverpulseapplication@tourism.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Viewer
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                2 days ago
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">12</span> users
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left h-5 w-5"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            1
                        </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            2
                        </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            3
                        </a>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            4
                        </a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right h-5 w-5"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addUserBtn = document.getElementById('addUserBtn');
        const addUserModal = document.getElementById('addUserModal');
        const closeModal = document.getElementById('closeModal');
        const addUserForm = document.getElementById('addUserForm');
        const modalTitle = document.getElementById('modalTitle');
        const submitButton = document.getElementById('submitButton');
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        
        let currentEditRow = null;
        let currentDeleteRow = null;
        
        // Open modal in add mode
        addUserBtn.addEventListener('click', function() {
            resetForm();
            modalTitle.textContent = 'Add New User';
            submitButton.textContent = 'Add User';
            addUserModal.classList.remove('hidden');
        });
        
        // Close modal
        closeModal.addEventListener('click', function() {
            addUserModal.classList.add('hidden');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addUserModal) {
                addUserModal.classList.add('hidden');
            }
            if (event.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
        });
        
        // Close delete modal
        cancelDelete.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
        });
        
        // Confirm delete
        confirmDelete.addEventListener('click', function() {
            if (currentDeleteRow) {
                currentDeleteRow.remove();
                updatePaginationCount(-1);
                showToast('User deleted successfully!', 'success');
                deleteModal.classList.add('hidden');
                currentDeleteRow = null;
            }
        });
        
        // Form submission (for both add and edit)
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userName = document.getElementById('userName').value;
            const userEmail = document.getElementById('userEmail').value;
            const userRole = document.getElementById('userRole').value;
            const editUserId = document.getElementById('editUserId').value;
            
            // Here you would typically send this data to the backend via AJAX
            console.log('User data:', { name: userName, email: userEmail, role: userRole, id: editUserId });
            
            if (editUserId && currentEditRow) {
                // Update existing user
                updateUserInTable(currentEditRow, userName, userEmail, userRole);
                showToast('User updated successfully!', 'success');
            } else {
                // Add new user
                addUserToTable(userName, userEmail, userRole);
                showToast('User added successfully!', 'success');
            }
            
            // Close modal and reset form
            addUserModal.classList.add('hidden');
            resetForm();
        });
        
        // Set up event delegation for Edit and Delete buttons
        document.querySelector('table tbody').addEventListener('click', function(e) {
            const target = e.target;
            
            // Handle Edit button click
            if (target.textContent === 'Edit' || (target.parentElement && target.parentElement.textContent === 'Edit')) {
                const row = target.closest('tr');
                if (row) {
                    openEditModal(row);
                }
            }
            
            // Handle Delete button click
            if (target.textContent === 'Delete' || (target.parentElement && target.parentElement.textContent === 'Delete')) {
                const row = target.closest('tr');
                if (row) {
                    openDeleteModal(row);
                }
            }
        });
        
        function addUserToTable(name, email, role) {
            const tbody = document.querySelector('table tbody');
            
            // Create initials from name
            const initials = name.split(' ').map(word => word[0]).join('').toUpperCase();
            
            // Determine badge color based on role
            let badgeClass = '';
            if (role === 'admin') {
                badgeClass = 'bg-blue-100 text-blue-800';
            } else if (role === 'user') {
                badgeClass = 'bg-indigo-100 text-indigo-800';
            } else {
                badgeClass = 'bg-yellow-100 text-yellow-800';
            }
            
            // Format role for display
            const displayRole = role.charAt(0).toUpperCase() + role.slice(1);
            
            // Create unique ID for the row
            const rowId = 'user-' + Date.now();
            
            const newRow = document.createElement('tr');
            newRow.id = rowId;
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                            <span class="text-gray-600 font-medium">${initials}</span>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${name}</div>
                            <div class="text-sm text-gray-500">${email}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}">
                        ${displayRole}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Active
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    Just now
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                    <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            
            // Insert at the beginning of the table
            tbody.insertBefore(newRow, tbody.firstChild);
            
            // Update the count in the pagination
            updatePaginationCount(1);
        }
        
        function updateUserInTable(row, name, email, role) {
            // Create initials from name
            const initials = name.split(' ').map(word => word[0]).join('').toUpperCase();
            
            // Determine badge color based on role
            let badgeClass = '';
            if (role === 'admin') {
                badgeClass = 'bg-blue-100 text-blue-800';
            } else if (role === 'user') {
                badgeClass = 'bg-indigo-100 text-indigo-800';
            } else {
                badgeClass = 'bg-yellow-100 text-yellow-800';
            }
            
            // Format role for display
            const displayRole = role.charAt(0).toUpperCase() + role.slice(1);
            
            // Update user info
            const userInfo = row.querySelector('td:first-child');
            userInfo.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="text-gray-600 font-medium">${initials}</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${name}</div>
                        <div class="text-sm text-gray-500">${email}</div>
                    </div>
                </div>
            `;
            
            // Update role badge
            const roleBadge = row.querySelector('td:nth-child(2) span');
            roleBadge.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}`;
            roleBadge.textContent = displayRole;
        }
        
        function openEditModal(row) {
            const nameElement = row.querySelector('.text-sm.font-medium.text-gray-900');
            const emailElement = row.querySelector('.text-sm.text-gray-500');
            const roleElement = row.querySelector('td:nth-child(2) span');
            
            if (nameElement && emailElement && roleElement) {
                const name = nameElement.textContent.trim();
                const email = emailElement.textContent.trim();
                let role = roleElement.textContent.trim().toLowerCase();
                
                document.getElementById('userName').value = name;
                document.getElementById('userEmail').value = email;
                document.getElementById('userRole').value = role;
                document.getElementById('editUserId').value = row.id || '';
                
                modalTitle.textContent = 'Edit User';
                submitButton.textContent = 'Save Changes';
                
                currentEditRow = row;
                addUserModal.classList.remove('hidden');
            }
        }
        
        function openDeleteModal(row) {
            currentDeleteRow = row;
            deleteModal.classList.remove('hidden');
        }
        
        function resetForm() {
            addUserForm.reset();
            document.getElementById('editUserId').value = '';
            currentEditRow = null;
        }
        
        function updatePaginationCount(change = 0) {
            const countElement = document.querySelector('.text-gray-700 .font-medium:last-child');
            if (countElement) {
                let count = parseInt(countElement.textContent) + change;
                countElement.textContent = count.toString();
            }
        }
        
        // Toast notification function
        function showToast(message, type = 'info') {
            // Create toast element if it doesn't exist
            let toast = document.getElementById('toast-notification');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toast-notification';
                toast.className = 'fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out translate-y-[-100%] opacity-0';
                document.body.appendChild(toast);
            }
            
            // Set toast style based on type
            let bgColor = 'bg-gray-800';
            if (type === 'success') bgColor = 'bg-green-600';
            if (type === 'error') bgColor = 'bg-red-600';
            if (type === 'warning') bgColor = 'bg-yellow-600';
            
            toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out z-50 text-white ${bgColor} translate-y-[-100%] opacity-0`;
            toast.textContent = message;
            
            // Show toast
            setTimeout(() => {
                toast.classList.replace('translate-y-[-100%]', 'translate-y-0');
                toast.classList.replace('opacity-0', 'opacity-100');
            }, 10);
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.replace('translate-y-0', 'translate-y-[-100%]');
                toast.classList.replace('opacity-100', 'opacity-0');
            }, 3000);
        }
    });
</script>
@endsection
