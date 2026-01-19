from playwright.sync_api import sync_playwright
import time

def run():
    with sync_playwright() as p:
        # Launch with fake media stream to bypass permission prompt
        browser = p.chromium.launch(args=["--use-fake-ui-for-media-stream", "--use-fake-device-for-media-stream"])
        context = browser.new_context(permissions=["microphone"])
        page = context.new_page()

        print("Navigating to Login...")
        page.goto("http://localhost:8080/src/pages/login.php")
        page.fill("input[name='email']", "student@jules.com")
        page.fill("input[name='password']", "student123")
        page.click("button[type='submit']")

        print("Waiting for Dashboard...")
        page.wait_for_url("**/student/dashboard.php")

        print("Starting Exam...")
        # Try to find "Start Full Exam"
        try:
            page.click("text=Start Full Exam", timeout=5000)
            page.wait_for_load_state('networkidle')
            page.click("text=I'm Ready - Start", timeout=5000)
            page.wait_for_load_state('networkidle')
        except Exception as e:
            print(f"Could not start new exam: {e}. Trying to continue or finding submission ID...")
            # If we are already in an exam, we might be redirected.
            # Or we can try to force navigate to Part 3 if we know the submission ID.
            # But we don't know the ID easily.
            # Let's assume the happy path for now, or use the last submission ID from DB if needed.

        # Force Navigate to Part 3 (We need submission_id)
        # The URL should be runner.php?submission_id=X&...
        url = page.url
        print(f"Current URL: {url}")

        if "submission_id=" in url:
            sub_id = url.split("submission_id=")[1].split("&")[0]
            print(f"Submission ID: {sub_id}")

            # Go to Part 3
            part3_url = f"http://localhost:8080/src/pages/exam/runner.php?submission_id={sub_id}&mode=full&part=3"
            print(f"Navigating to Part 3: {part3_url}")
            page.goto(part3_url)

            # Verify Prep Phase
            print("Checking for Prep Overlay...")
            try:
                page.wait_for_selector("#overlay:not(.hidden)", timeout=5000)
                overlay_text = page.inner_text("#overlay-text")
                print(f"Overlay Text: {overlay_text}")
                if "Preparation Starts In" in overlay_text:
                    print("PASS: Overlay correct.")
                else:
                    print("FAIL: Overlay text incorrect.")
            except:
                print("FAIL: Overlay not found or timed out.")

            # Wait for overlay to disappear
            time.sleep(4)

            # Verify Timer Label
            try:
                label = page.inner_text("#timer-label")
                print(f"Timer Label: {label}")
                if "Preparation" in label:
                    print("PASS: Timer Label correct (Preparation Time).")
                else:
                    print("FAIL: Timer Label incorrect.")
            except:
                print("FAIL: Timer Label not found.")

            # Verify Timer Value (Should be around 60)
            try:
                timer_val = page.inner_text("#timer-text")
                print(f"Timer Value: {timer_val}")
                if int(timer_val) > 30 and int(timer_val) <= 60:
                     print("PASS: Timer value seems correct for 1 min prep.")
                else:
                     print(f"FAIL: Timer value {timer_val} is unexpected (expected ~60).")
            except:
                print("FAIL: Timer value error.")

            page.screenshot(path="verify_part3_recheck.png")

        else:
            print("Could not retrieve submission ID.")
            page.screenshot(path="verify_fail.png")

        browser.close()

if __name__ == "__main__":
    run()
