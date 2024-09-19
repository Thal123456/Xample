<?php
include 'php/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main>
            <div class="four-box-container">
                <h1>Dashboard</h1>
                <div class="breadcrumb">
                    <a href="#">Dashboard</a>
                    <i class='bx bx-chevron-right'></i>
                    <a class="active" href="#">Home</a>
                </div>
            </div>

            <!-------
            <div class="box-info-container">
                <div class="datetime-display">
                    <div id="currentDate" class="current-date"></div>
                    <div id="currentTime" class="current-time"></div>
                </div>
            </div> -------->

            <div class="box-info-container">
                <div class="box-info">
                    <i class='bx bxs-calendar-check'></i>
                    <span class="text">
                        <?php
                        $today_date = date('Y-m-d');

                        $totalGuestsQuery1 = "SELECT SUM(Totalguest) as total_guests FROM room_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $totalGuestsResult1 = $conn->query($totalGuestsQuery1);
                        $totalGuestsRow1 = $totalGuestsResult1->fetch_assoc();
                        $totalGuests1 = $totalGuestsRow1['total_guests'] ?? 0;

                        $totalGuestsQuery2 = "SELECT SUM(numguest) as total_guests FROM facility_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $totalGuestsResult2 = $conn->query($totalGuestsQuery2);
                        $totalGuestsRow2 = $totalGuestsResult2->fetch_assoc();
                        $totalGuests2 = $totalGuestsRow2['total_guests'] ?? 0;

                        $totalGuests = $totalGuests1 + $totalGuests2;
                        ?>
                        <h3><?php echo $totalGuests; ?></h3>
                        <p>Check-in</p>
                    </span>
                </div>
                <div class="box-info">
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <?php
                        $today_date = date('Y-m-d');
                        $occupied_room_query = "SELECT * FROM room_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $result = mysqli_query($conn, $occupied_room_query);

                        if ($result) {
                            $occupied_rooms_today = mysqli_num_rows($result);
                        } else {
                            $occupied_rooms_today = 0;
                        }
                        ?>
                        <h3><?php echo htmlspecialchars($occupied_rooms_today); ?></h3>
                        <p>Occupied Rooms</p>
                    </span>
                </div>
                <div class="box-info">
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <?php
                        $today_date = date('Y-m-d');
                        $occupied_facility_query = "SELECT * FROM facility_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $result2 = mysqli_query($conn, $occupied_facility_query);

                        if ($result2) {
                            $occupied_facility_today = mysqli_num_rows($result2);
                        } else {
                            $occupied_facility_today = 0;
                        }
                        ?>
                        <h3><?php echo htmlspecialchars($occupied_facility_today); ?></h3>
                        <p>Occupied facilities</p>
                    </span>
                </div>
                <div class="box-info">
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <h3>?</h3>
                        <p>?</p>
                    </span>
                </div>
            </div>


            <div class="box-info-container">
                <div class="box-info">
                    <div class="room_statistics">
                        <div class="head">
                            <h4>Room Statistics</h4>
                        </div>
                        <canvas id="roomStatsChart"></canvas>
                        <?php
                        $today = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                        $weekAgo = date('Y-m-d', strtotime($today . ' -6 days'));

                        $roomStatsQuery = "SELECT 
                            DAYNAME(checkin) as day_of_week,
                            COUNT(*) as occupied_rooms,
                            SUM(Totalguest) as total_guests
                        FROM room_reservation
                        WHERE checkin BETWEEN '$weekAgo' AND '$today'
                        AND status = 'Reserved' or status = 'checkin' or status = 'checkout' 
                        GROUP BY DAYNAME(checkin)
                        ORDER BY FIELD(DAYNAME(checkin), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

                        $roomStatsResult = $conn->query($roomStatsQuery);

                        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        $roomStats = array_fill_keys($allDays, ['occupied_rooms' => 0, 'total_guests' => 0]);

                        while ($row = $roomStatsResult->fetch_assoc()) {
                            $roomStats[$row['day_of_week']] = [
                                'occupied_rooms' => $row['occupied_rooms'],
                                'total_guests' => $row['total_guests']
                            ];
                        }

                        $roomDaysOfWeek = array_keys($roomStats);
                        $occupiedRooms = array_column($roomStats, 'occupied_rooms');
                        $roomTotalGuests = array_column($roomStats, 'total_guests');

                        $nextWeek = date('Y-m-d', strtotime($today . ' +7 days'));
                        $prevWeek = date('Y-m-d', strtotime($today . ' -7 days'));
                        ?>
                    </div>
                </div>


                <div class="box-info">
                    <div class="calendar">
                        <div class="head">
                            <h4>Calendar</h4>
                        </div>
                        <div id="calendar"></div>
                        <?php
                        $reservationDatesQuery = "SELECT DISTINCT date FROM (
                            SELECT checkin AS date FROM room_reservation WHERE status = 'Reserved'
                            UNION
                            SELECT checkout AS date FROM room_reservation WHERE status = 'Reserved'
                            UNION
                            SELECT checkin AS date FROM facility_reservation WHERE status = 'Reserved'
                            UNION
                            SELECT checkout AS date FROM facility_reservation WHERE status = 'Reserved'
                        ) AS combined_dates";
                        $reservationDatesResult = $conn->query($reservationDatesQuery);
                        $reservationDates = [];
                        while ($row = $reservationDatesResult->fetch_assoc()) {
                            $reservationDates[] = $row['date'];
                        }
                        ?>
                        <script>
                            const reservationDates = <?php echo json_encode($reservationDates); ?>;
                        </script>
                    </div>
                </div>
            </div>

            <div class="box-info-container">
                <div class="box-info">
                    <?php
                    $today = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                    $weekAgo = date('Y-m-d', strtotime($today . ' -6 days'));

                    $facilityStatsQuery = "SELECT 
                        DAYNAME(checkin) as day_of_week,
                        COUNT(*) as reserved_facilities,
                        SUM(numguest) as total_guests
                    FROM facility_reservation
                    WHERE checkin BETWEEN '$weekAgo' AND '$today'
                    AND status = 'Reserved'
                    GROUP BY DAYNAME(checkin)
                    ORDER BY FIELD(DAYNAME(checkin), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

                    $facilityStatsResult = $conn->query($facilityStatsQuery);

                    $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $facilityStats = array_fill_keys($allDays, ['reserved_facilities' => 0, 'total_guests' => 0]);

                    while ($row = $facilityStatsResult->fetch_assoc()) {
                        $facilityStats[$row['day_of_week']] = [
                            'reserved_facilities' => $row['reserved_facilities'],
                            'total_guests' => $row['total_guests']
                        ];
                    }

                    $facilityDaysOfWeek = array_keys($facilityStats);
                    $reservedFacilities = array_column($facilityStats, 'reserved_facilities');
                    $facilityTotalGuests = array_column($facilityStats, 'total_guests');

                    $nextWeek = date('Y-m-d', strtotime($today . ' +7 days'));
                    $prevWeek = date('Y-m-d', strtotime($today . ' -7 days'));
                    ?>

                    <div class="facility_statistics">
                        <div class="head">
                            <h4>Facility Statistics</h4>
                           
                        </div>
                        <canvas id="facilityStatsChart"></canvas>
                    </div>
                </div>

                <div class="box-info">
                    <div class="Summary">
                        <div class="head">
                            <h4>Summary</h4>
                        </div>
                        <!-- Content for Todos -->
                    </div>
                </div>
            </div>

        </main>
        <!-- MAIN -->
        </section>
        <!-- CONTENT -->

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"
            integrity="sha512-8Z5++K1rB3U+USaLKG6oO8uWWBhdYsM3hmdirnOEWp8h2B1aOikj5zBzlXs8QOrvY9OxEnD2QDkbSKKpfqcIWw=="
            crossorigin="anonymous"></script>
        <script src="assets/js/experiment.js"></script>
        <script src="assets/js/roombooking.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendar = document.getElementById('calendar');
                const currentDate = new Date();
                const currentMonth = currentDate.getMonth();
                const currentYear = currentDate.getFullYear();

                function generateCalendar(month, year) {
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const daysInMonth = lastDay.getDate();
                    const startingDay = firstDay.getDay();

                    let calendarHTML = ` 
                <div class="calendar-header">
                    <button onclick="changeMonth(-1)">&lt;</button>
                    <h3>${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}</h3>
                    <button onclick="changeMonth(1)">&gt;</button>
                </div>
                <table>
                    <tr>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                    </tr>
                    <tr>
            `;

                    let day = 1;
                    for (let i = 0; i < 6; i++) {
                        for (let j = 0; j < 7; j++) {
                            if (i === 0 && j < startingDay) {
                                calendarHTML += '<td></td>';
                            } else if (day > daysInMonth) {
                                break;
                            } else {
                                const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                const hasReservation = reservationDates.includes(date) ? 'has-reservation' : '';
                                calendarHTML += `<td class="${hasReservation}">${day}</td>`;
                                day++;
                            }
                        }
                        if (day > daysInMonth) {
                            break;
                        }
                        calendarHTML += '</tr><tr>';
                    }

                    calendarHTML += '</tr></table>';
                    calendar.innerHTML = calendarHTML;
                }

                generateCalendar(currentMonth, currentYear);

                window.changeMonth = function (delta) {
                    currentDate.setMonth(currentDate.getMonth() + delta);
                    generateCalendar(currentDate.getMonth(), currentDate.getFullYear());
                };
            });
        </script>

        <!----Start of room statistic----->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ... existing calendar code ...

                // Room Statistics Chart
                const roomCtx = document.getElementById('roomStatsChart').getContext('2d');
                new Chart(roomCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($roomDaysOfWeek); ?>,
                        datasets: [{
                            label: 'Occupied Rooms',
                            data: <?php echo json_encode($occupiedRooms); ?>,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Total Check-ins',
                            data: <?php echo json_encode($roomTotalGuests); ?>,
                            backgroundColor: 'rgba(255, 159, 64, 0.6)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Day of Week'
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Room Reservations - Week of <?php echo $weekAgo; ?> to <?php echo $today; ?>'
                            }
                        }
                    }
                });
            });
        </script>
        <!------End of room statistic----->
        <!----Start of facility statistic----->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const facilityCtx = document.getElementById('facilityStatsChart').getContext('2d');
                new Chart(facilityCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($facilityDaysOfWeek); ?>,
                        datasets: [{
                            label: 'Reserved Facilities',
                            data: <?php echo json_encode($reservedFacilities); ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Total Guests',
                            data: <?php echo json_encode($facilityTotalGuests); ?>,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Day of Week'
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Facility Reservations - Week of <?php echo $weekAgo; ?> to <?php echo $today; ?>'
                            }
                        }
                    }
                });
            });
        </script>
        <!----End of facility statistic----->


        <style>
            .calendar {
                display: flex;
                flex-direction: column;
                height: 100%;
                width: 100%;
            }

            .head {
                margin-bottom: 10px;
            }

            #calendar {
                font-family: Arial, sans-serif;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }

            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .calendar-header button {
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
                padding: 5px 10px;
            }

            .calendar-header h3 {
                margin: 0;
                white-space: nowrap;
            }

            table {
                width: 100%;
                height: 100%;
                border-collapse: collapse;
                flex-grow: 1;
            }

            th,
            td {
                text-align: center;
                padding: 10px 5px;
                border: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
            }

            .has-reservation {
                background-color: #ffeb3b;
                font-weight: bold;
            }

            @media (max-width: 600px) {

                th,
                td {
                    padding: 5px 2px;
                    font-size: 0.9em;
                }
            }

            /***Room/facility statistic */
            .room_statistics,
            .facility_statistics {
                width: 100%;
                height: 300px;
                /* Adjust as needed */
            }

            #roomStatsChart,
            #facilityStatsChart {
                width: 100%;
                height: 100%;
            }

            .box-info-container {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 20px;
            }

            .box-info {
                flex: 1;
                min-width: 200px;
                background-color: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
            }

            .box-info i {
                font-size: 32px;
                margin-right: 15px;
            }

            .box-info .text {
                flex-grow: 1;
            }

            .box-info h3 {
                margin: 0;
                font-size: 24px;
            }

            .box-info p {
                margin: 5px 0 0;
                color: #888;
            }

            @media (max-width: 768px) {
                .box-info {
                    min-width: calc(50% - 10px);
                }
            }

            @media (max-width: 480px) {
                .box-info {
                    min-width: 100%;
                }
            }

            .week-navigation {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .btn {
                padding: 5px 10px;
                text-decoration: none;
                color: #fff;
                background-color: #007bff;
                border-radius: 5px;
            }

            .btn:hover {
                background-color: #0056b3;
            }
        </style>

</body>

</html>

</html>