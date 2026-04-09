<div class="container">
    <div class="glass-card">
        <h1>Notify</h1>
        <p class="subtitle">Get instant email alerts for new GitHub releases</p>
        
        <form id="subscribeForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" placeholder="you@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="repository">GitHub Repository</label>
                <input type="text" id="repository" placeholder="owner/repo (e.g. golang/go)" required>
            </div>

            <div class="form-group">
                <label for="apiKey">API Access Key</label>
                <input type="password" id="apiKey" placeholder="Enter key (default: my-secret-key)">
            </div>

            <button type="submit" id="submitBtn" class="btn-submit">
                <span id="loader" class="loader"></span>
                <span id="btnText">Subscribe Now</span>
            </button>
        </form>

        <div id="statusMessage" class="status-message"></div>
        <div style="margin-top: 20px; text-align: center; font-size: 0.8rem; opacity: 0.6;">
            <a href="/api/docs" style="color: inherit;">API Documentation</a> | <a href="/metrics" style="color: inherit;">Metrics</a>
        </div>
    </div>
</div>
<script src="/assets/app.js"></script>
