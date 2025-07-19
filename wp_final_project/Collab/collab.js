document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const userTable = document.getElementById('userTable');
    const tableBody = userTable.querySelector('tbody');
    const originalRows = Array.from(tableBody.rows);
    
    // Search functionality
    function searchUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        
        tableBody.innerHTML = '';
        
        if (searchTerm === '') {
            // Show all users if search is empty
            originalRows.forEach(row => tableBody.appendChild(row.cloneNode(true)));
        } else {
            // Filter users based on search term
            originalRows.forEach(row => {
                const username = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    tableBody.appendChild(row.cloneNode(true));
                }
            });
        }
        
        // Reattach event listeners to new buttons
        attachInviteButtonListeners();
    }
    
    // Handle invite button click
    function handleInviteClick(event) {
        const button = event.target;
        const userId = button.getAttribute('data-user-id');
        
        // Redirect to your invitation page with the user ID
        window.location.href = `invit_collaboration.php?user_id=${userId}`;
    }
    
    // Attach event listeners to all invite buttons
    function attachInviteButtonListeners() {
        document.querySelectorAll('.invite-btn').forEach(button => {
            // Remove any existing listeners to prevent duplicates
            button.removeEventListener('click', handleInviteClick);
            button.addEventListener('click', handleInviteClick);
        });
    }
    
    // Initial attachment of event listeners
    attachInviteButtonListeners();
    
    // Event listeners
    searchButton.addEventListener('click', searchUsers);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchUsers();
        }
    });
});document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const userTable = document.getElementById('userTable');
    const tableBody = userTable.querySelector('tbody');
    const originalRows = Array.from(tableBody.rows);
    
    // Search functionality
    function searchUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        
        tableBody.innerHTML = '';
        
        if (searchTerm === '') {
            // Show all users if search is empty
            originalRows.forEach(row => tableBody.appendChild(row.cloneNode(true)));
        } else {
            // Filter users based on search term
            originalRows.forEach(row => {
                const username = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    tableBody.appendChild(row.cloneNode(true));
                }
            });
        }
        
        // Reattach event listeners to new buttons
        attachInviteButtonListeners();
    }
    
    // Handle invite button click
    function handleInviteClick(event) {
        const button = event.target;
        const userId = button.getAttribute('data-user-id');
        
        // Redirect to your invitation page with the user ID
        window.location.href = `invit_collaboration.php?user_id=${userId}`;
    }
    
    // Attach event listeners to all invite buttons
    function attachInviteButtonListeners() {
        document.querySelectorAll('.invite-btn').forEach(button => {
            // Remove any existing listeners to prevent duplicates
            button.removeEventListener('click', handleInviteClick);
            button.addEventListener('click', handleInviteClick);
        });
    }
    
    // Initial attachment of event listeners
    attachInviteButtonListeners();
    
    // Event listeners
    searchButton.addEventListener('click', searchUsers);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchUsers();
        }
    });
});