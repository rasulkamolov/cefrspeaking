from playwright.sync_api import sync_playwright

def verify_mobile_ui():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        # Mobile Viewport (iPhone 12/13 Pro)
        context = browser.new_context(viewport={'width': 390, 'height': 844}, user_agent='Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1')
        page = context.new_page()

        print("Navigating to Login...")
        page.goto("http://127.0.0.1:8080/pages/login.php")
        page.screenshot(path="mobile_login.png")

        print("Logging in...")
        page.fill("input[name='email']", "student@jules.com")
        page.fill("input[name='password']", "student123")
        page.click("button[type='submit']")
        page.wait_for_url("**/student/dashboard.php")

        print("Dashboard Loaded. Taking screenshot...")
        page.screenshot(path="mobile_dashboard.png")

        # Verify Bottom Nav exists
        if page.is_visible("nav.fixed.bottom-0"):
            print("Bottom Nav detected.")
        else:
            print("ERROR: Bottom Nav not found.")

        # Start Exam 1 Part 1.2 (to check 2 images)
        print("Starting Exam 1 Part 1.2...")
        # Find the first Part 1.2 link
        page.locator("a[href*='part=1.2']").first.click()
        page.wait_for_url("**/exam/intro.php*")
        page.click("text=Start Now")
        page.wait_for_url("**/exam/runner.php*")

        print("Exam Runner (Part 1.2) Loaded. Taking screenshot...")
        # Wait for images to load (optional, but good)
        page.wait_for_timeout(2000)
        page.screenshot(path="mobile_exam_1_2.png")

        browser.close()

if __name__ == "__main__":
    verify_mobile_ui()
