
from playwright.sync_api import sync_playwright

def verify_updates():
    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=[
                "--use-fake-ui-for-media-stream",
                "--use-fake-device-for-media-stream"
            ]
        )
        context = browser.new_context()
        context.grant_permissions(['microphone'], origin='http://localhost:8080')
        page = context.new_page()

        # Listen for console logs
        page.on("console", lambda msg: print(f"CONSOLE: {msg.text}"))
        page.on("pageerror", lambda err: print(f"PAGE ERROR: {err}"))

        # 1. Login
        print("Logging in...")
        page.goto("http://localhost:8080/src/pages/login.php")

        # Verify Title
        title = page.title()
        print(f"Login Page Title: {title}")
        if "Oxford CEFR Speaking" not in title:
            print("FAILED: Title incorrect")

        page.fill("input[name='email']", "student@jules.com")
        page.fill("input[name='password']", "student123")
        page.click("button[type='submit']")
        page.wait_for_url("**/dashboard.php")
        print("Logged in.")

        # 2. Verify Part 2 Bullet Points
        print("Navigating to Part 2...")
        page.goto("http://localhost:8080/src/pages/exam/intro.php?test_id=1&mode=part&part=2")
        page.click("text=I'm Ready - Start")
        page.wait_for_url("**/runner.php*")

        print("Waiting for Part 2 content...")
        # Should have a UL with LI
        page.wait_for_selector("ul.list-disc")
        page.screenshot(path="verification_part2_bullets.png")
        print("Part 2 screenshot taken.")

        # 3. Verify Part 3 Prep Phase
        print("Navigating to Part 3...")
        page.goto("http://localhost:8080/src/pages/exam/intro.php?test_id=1&mode=part&part=3")
        page.click("text=I'm Ready - Start")
        page.wait_for_url("**/runner.php*")

        print("Waiting for Part 3 content...")
        # Prep phase: Button should be yellow/disabled or visual indicator.
        # My code adds class 'bg-yellow-500' to actionBtn.
        page.wait_for_selector("#action-btn.bg-yellow-500", timeout=10000)

        # Check text if possible (optional)

        page.screenshot(path="verification_part3_prep.png")
        print("Part 3 Prep Phase screenshot taken.")

        browser.close()

if __name__ == "__main__":
    verify_updates()
