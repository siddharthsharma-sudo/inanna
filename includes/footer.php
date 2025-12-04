<hr>
<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-logo">
            <img src="assets/images/footer_logo.webp" alt="World of INANNA Logo">
        </div>

        <div class="footer-links-wrapper">
            
            <div class="footer-column">
                <h3 class="column-title">About</h3>
                <ul class="link-list">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">WOI</a></li>
                    <li><a href="#">Appointment</a></li>
                    <li><a href="#">Shop</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="column-title">Consumer Policy</h3>
                <ul class="link-list">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                    <li><a href="#">Refund and Returns Policy</a></li>
                    <li><a href="#">Shipping and Handling Policy</a></li>
                </ul>
            </div>

            <div class="footer-column contact-column">
                <h3 class="column-title">Get In Touch</h3>
                
                <div class="contact-item">
                    <span class="icon">&#9993;</span> <a href="mailto:worldofinanna@gmail.com">worldofinanna@gmail.com</a>
                </div>

                <div class="contact-item">
                    <span class="icon">&#9906;</span> <address>Jhajra, Dehradun 248007</address>
                </div>

                <div class="contact-item">
                    <span class="icon">&#9742;</span> <a href="tel:7456000222">7456000222</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* -------------------------------------------------------------------------- */
/* FOOTER CSS STYLES */
/* -------------------------------------------------------------------------- */

.main-footer {
    background-color: #8B0000; /* Dark Maroon/Red background color */
    color: white;
    padding: 40px 0;
    font-family: Arial, sans-serif;
}

.footer-container {
    max-width: 1400px; /* Adjust to match your website's main content width */
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between; /* Pushes the logo left and links to the right */
    align-items: flex-start;
}

/* --- Logo Styling --- */
.footer-logo {
    flex-shrink: 0; /* Ensures logo doesn't shrink */
    padding-right: 50px;
    /* Adjust margin to align the logo's image to the text columns */
    padding-top: 15px; 
}

.footer-logo img {
    /* The provided image shows the logo clearly, adjust size as needed */
    width: 150px; 
    height: auto;
     
}

/* --- Links Wrapper (Holds all link columns) --- */
.footer-links-wrapper {
    display: flex;
    gap: 80px; /* Space between the main columns */
    flex-grow: 1;
    justify-content: center; /* Ensures columns are aligned to the right */
}

.footer-column {
    flex-shrink: 0;
    min-width: 150px; /* Minimum width for each column */
}

.column-title {
    font-size: 1.1em;
    font-weight: bold;
    margin-bottom: 20px;
    text-transform: uppercase;
}

/* --- Link List Styling --- */
.link-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.link-list li {
    margin-bottom: 10px;
}

.link-list a {
    color: white;
    text-decoration: none;
    font-size: 0.95em;
    transition: color 0.2s;
}

.link-list a:hover {
    color: #f0f0f0; 
}

/* --- Contact Column Styling --- */
.contact-column {
    min-width: 220px; /* Needs slightly more space for the address */
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    font-size: 0.95em;
}

.contact-item .icon {
    font-size: 1.2em;
    margin-right: 10px;
    /* Use CSS to style the 'icon' similar to the circles in the image */
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 30px;
    height: 30px;
    border: 1px solid white; 
    border-radius: 50%;
}

.contact-item a, .contact-item address {
    color: white;
    text-decoration: none;
    font-style: normal; /* Removes address tag italics */
    line-height: 1.4;
}

/* --- Responsive Adjustments --- */
@media (max-width: 1100px) {
    .footer-links-wrapper {
        gap: 40px;
    }
}

@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-logo {
        padding: 0 0 30px 0;
    }

    .footer-links-wrapper {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 30px;
    }

    .footer-column {
        min-width: auto;
        width: 100%;
    }

    .contact-item {
        justify-content: center;
        text-align: left; /* Keep text aligned left within the item for readability */
    }
}
</style>
<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    <p class="mb-0">© <?php echo date("Y"); ?> My Shop. All Rights Reserved.</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Your JS -->
<script src="/ecommerce/assets/js/script.js"></script>

</body>
</html>
