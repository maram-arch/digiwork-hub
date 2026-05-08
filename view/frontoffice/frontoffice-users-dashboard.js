(function() {
    let allStats = window.initialStats || {
        candidat: 0,
        entreprise: 0,
        sponsor: 0,
        admin: 0,
        total: 0
    };

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
    });

    function initializeDashboard() {
        // Statistics are already loaded from server
        renderCharts();

        // Setup search event listener
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', performSearch);
        }

        // Setup sort event listener
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', performSort);
        }

        // Update user count display
        const rows = document.querySelectorAll('.user-row');
        document.getElementById('userCount').textContent = '(' + rows.length + ')';
    }

    function renderCharts() {
        const total = allStats.total || 1;

        // Clear old chart instances
        if (document.getElementById('chartPieDistribution')) {
            document.getElementById('chartPieDistribution').innerHTML = '';
        }

        // Pie Chart pour la distribution
        if (document.getElementById('chartPieDistribution') && typeof ApexCharts !== 'undefined') {
            console.log('Creating pie chart with stats:', allStats);
            new ApexCharts(document.getElementById('chartPieDistribution'), {
                series: [allStats.candidat, allStats.entreprise, allStats.sponsor, allStats.admin],
                chart: { 
                    type: 'donut',
                    height: 300
                },
                labels: ['Candidats', 'Entreprises', 'Sponsors', 'Admins'],
                colors: ['#1e40af', '#f5576c', '#00f2fe', '#fee140'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px'
                                },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    fontWeight: 600
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    }
                },
                legend: {
                    position: 'bottom'
                }
            }).render();
        }
    }

    function performSearch() {
        const searchInput = document.getElementById('searchInput');
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.user-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const id = row.getAttribute('data-id');
            const email = row.getAttribute('data-email');

            // Check if search term matches ID or email
            const matches = id.includes(searchTerm) || email.includes(searchTerm);

            if (searchTerm === '' || matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show filtered count or total count
        if (searchTerm === '') {
            document.getElementById('userCount').textContent = '(' + allStats.total + ')';
        } else {
            document.getElementById('userCount').textContent = '(' + visibleCount + ' filtré)';
        }

        // Show "no results" message if needed
        const tbody = document.getElementById('usersTableBody');
        const noResultsRow = tbody.querySelector('.no-results');
        if (noResultsRow) {
            noResultsRow.remove();
        }

        if (visibleCount === 0 && searchTerm !== '') {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results';
            noResultsRow.innerHTML = '<td colspan="5" class="text-center">Aucun utilisateur trouvé avec ces critères</td>';
            tbody.appendChild(noResultsRow);
        }
    }

    function performSort() {
        const sortSelect = document.getElementById('sortSelect');
        const sortValue = sortSelect.value;

        if (!sortValue) {
            return;
        }

        const rows = Array.from(document.querySelectorAll('.user-row'));
        const tbody = document.getElementById('usersTableBody');

        // Sort based on selected value
        rows.sort((a, b) => {
            if (sortValue === 'id-asc') {
                return parseInt(a.getAttribute('data-id')) - parseInt(b.getAttribute('data-id'));
            } else if (sortValue === 'id-desc') {
                return parseInt(b.getAttribute('data-id')) - parseInt(a.getAttribute('data-id'));
            } else if (sortValue === 'email-asc') {
                return a.getAttribute('data-email').localeCompare(b.getAttribute('data-email'));
            } else if (sortValue === 'email-desc') {
                return b.getAttribute('data-email').localeCompare(a.getAttribute('data-email'));
            }
        });

        // Re-append sorted rows to tbody
        rows.forEach(row => {
            // Remove the row from DOM first
            row.parentNode.removeChild(row);
            // Re-append it in sorted order
            tbody.appendChild(row);
        });

        // Reset sort select after sorting
        setTimeout(() => {
            sortSelect.value = '';
        }, 100);
    }
})();
