<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selector de Tiempo Digital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #1a1a1a;
            font-family: 'Courier New', monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }

        .time-selector {
            background: #2a2a2a;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .time-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: 60px;
        }

        .time-scroll {
            height: 200px;
            overflow: hidden;
            position: relative;
            background: #333;
            border-radius: 10px;
            border: 2px solid #444;
        }

        .time-numbers {
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            padding: 10px 0;
        }

        .time-number {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }

        .time-number.active {
            color: #00ff88;
            font-size: 22px;
            text-shadow: 0 0 10px #00ff88;
        }

        .time-number:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .time-separator {
            font-size: 24px;
            color: #00ff88;
            font-weight: bold;
            margin: 0 10px;
            text-shadow: 0 0 10px #00ff88;
        }

        .am-pm-toggle {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-left: 20px;
        }

        .am-pm-btn {
            padding: 8px 15px;
            background: #333;
            border: 2px solid #444;
            color: #666;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-family: inherit;
            font-weight: bold;
        }

        .am-pm-btn.active {
            background: #00ff88;
            color: #000;
            border-color: #00ff88;
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.3);
        }

        .am-pm-btn:hover:not(.active) {
            color: #fff;
            border-color: #666;
        }

        .current-time {
            position: absolute;
            top: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 24px;
            color: #00ff88;
            text-shadow: 0 0 10px #00ff88;
        }

        .selection-indicator {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(0, 255, 136, 0.1);
            border: 2px solid #00ff88;
            border-radius: 5px;
            transform: translateY(-50%);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <?php
    // Generar las horas (1-12)
    $hours = range(1, 12);
    
    // Generar los minutos (00-59)

    
    // Obtener la hora actual
    $currentHour = (int)date('h');
    $currentMinute = (int)date('i');
    $currentPeriod = date('A');
    ?>

    <div class="time-selector">
        <div class="current-time" id="currentTime">
            <?php echo date('h:i A'); ?>
        </div>

        <!-- Columna de horas -->
        <div class="time-column">
            <div class="time-scroll" id="hoursScroll">
                <div class="selection-indicator"></div>
                <div class="time-numbers" id="hoursNumbers">
                    <?php foreach ($hours as $hour): ?>
                        <div class="time-number <?php echo $hour == $currentHour ? 'active' : ''; ?>" 
                             data-value="<?php echo $hour; ?>">
                            <?php echo sprintf("%02d", $hour); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="time-separator">:</div>



        <!-- Botones AM/PM -->
        <div class="am-pm-toggle">
            <button class="am-pm-btn <?php echo $currentPeriod == 'AM' ? 'active' : ''; ?>" 
                    data-period="AM">AM</button>
            <button class="am-pm-btn <?php echo $currentPeriod == 'PM' ? 'active' : ''; ?>" 
                    data-period="PM">PM</button>
        </div>
    </div>

    <script>
        class TimeSelector {
            constructor() {
                this.selectedHour = <?php echo $currentHour; ?>;
                this.selectedMinute = <?php echo $currentMinute; ?>;
                this.selectedPeriod = '<?php echo $currentPeriod; ?>';
                
                this.initializeScrollers();
                this.bindEvents();
                this.centerActiveItems();
            }

            initializeScrollers() {
                this.hoursContainer = document.getElementById('hoursNumbers');
                this.amPmButtons = document.querySelectorAll('.am-pm-btn');
            }

            bindEvents() {
                // Event listeners para números de hora
                this.hoursContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('time-number')) {
                        this.selectHour(parseInt(e.target.dataset.value));
                    }
                });

                // Event listeners para números de minutos


                // Event listeners para AM/PM
                this.amPmButtons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.selectPeriod(btn.dataset.period);
                    });
                });

                // Scroll con rueda del mouse
                document.getElementById('hoursScroll').addEventListener('wheel', (e) => {
                    e.preventDefault();
                    this.scrollHours(e.deltaY > 0 ? 1 : -1);
                });


            }

            selectHour(hour) {
                // Remover clase active de todas las horas
                this.hoursContainer.querySelectorAll('.time-number').forEach(el => {
                    el.classList.remove('active');
                });
                
                // Agregar clase active a la hora seleccionada
                const hourElement = this.hoursContainer.querySelector(`[data-value="${hour}"]`);
                if (hourElement) {
                    hourElement.classList.add('active');
                    this.selectedHour = hour;
                    this.centerElement(hourElement, this.hoursContainer);
                    this.updateCurrentTime();
                }
            }



            selectPeriod(period) {
                this.amPmButtons.forEach(btn => {
                    btn.classList.remove('active');
                });
                
                const selectedBtn = document.querySelector(`[data-period="${period}"]`);
                if (selectedBtn) {
                    selectedBtn.classList.add('active');
                    this.selectedPeriod = period;
                    this.updateCurrentTime();
                }
            }

            scrollHours(direction) {
                let newHour = this.selectedHour + direction;
                if (newHour > 12) newHour = 1;
                if (newHour < 1) newHour = 12;
                this.selectHour(newHour);
            }

            scrollMinutes(direction) {
                let newMinute = this.selectedMinute + direction;
                if (newMinute > 59) newMinute = 0;
                if (newMinute < 0) newMinute = 59;
                this.selectMinute(newMinute);
            }

            centerElement(element, container) {
                const elementTop = element.offsetTop;
                const containerHeight = container.parentElement.clientHeight;
                const elementHeight = element.clientHeight;
                const scrollTop = elementTop - (containerHeight / 2) + (elementHeight / 2);
                
                container.style.transform = `translateY(-${scrollTop}px)`;
            }

            centerActiveItems() {
                const activeHour = this.hoursContainer.querySelector('.active');
                
                if (activeHour) {
                    this.centerElement(activeHour, this.hoursContainer);
                }
                

            }

            updateCurrentTime() {
                const hourStr = this.selectedHour.toString().padStart(2, '0');
                const timeString = `${hourStr}: ${this.selectedPeriod}`;
                
                document.getElementById('currentTime').textContent = timeString;
            }
        }

        // Inicializar el selector cuando se carga la página
        document.addEventListener('DOMContentLoaded', () => {
            new TimeSelector();
        });

        // Actualizar la hora actual cada minuto
        setInterval(() => {
            const now = new Date();
            const currentTimeElement = document.getElementById('currentTime');
            if (!currentTimeElement.textContent.includes('Selected:')) {
                const hours = now.getHours();

                const ampm = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours % 12 || 12;
                
                
            }
        }, 60000);
    </script>
</body>
</html>