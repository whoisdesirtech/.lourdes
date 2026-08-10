import pty
import os
import subprocess
import select
import sys
import time

def run():
    sys.stdout.write("Initializing pseudo-terminal...\n")
    sys.stdout.flush()
    master_fd, slave_fd = pty.openpty()

    scratch_dir = "/Users/jeanfils/.gemini/antigravity/brain/04095ebc-e314-4994-9a05-6e2d68640839/scratch"
    output_path = os.path.join(scratch_dir, "pty_output.txt")
    code_path = os.path.join(scratch_dir, "code.txt")
    final_path = os.path.join(scratch_dir, "pty_final_output.txt")

    for path in [output_path, code_path, final_path]:
        if os.path.exists(path):
            os.remove(path)

    cwd = "/Users/jeanfils/Desktop/.Lourdes/web-app"
    sys.stdout.write(f"Starting Firebase CLI process in {cwd}...\n")
    sys.stdout.flush()
    
    proc = subprocess.Popen(
        ["npx", "-y", "firebase-tools@latest", "login", "--no-localhost"],
        stdin=slave_fd,
        stdout=slave_fd,
        stderr=slave_fd,
        close_fds=True,
        text=True,
        cwd=cwd
    )

    os.close(slave_fd)

    output = ""
    
    # Poll stdout for prompts or link (up to 30 seconds total)
    for _ in range(60):
        r, w, x = select.select([master_fd], [], [], 0.5)
        if master_fd in r:
            try:
                data = os.read(master_fd, 4096).decode('utf-8', errors='ignore')
                if not data:
                    break
                output += data
                
                # Print output incrementally to stdout
                sys.stdout.write(data)
                sys.stdout.flush()

                # Check if the incoming chunk contains a prompt
                if "(Y/n)" in data or "Y/n" in data:
                    sys.stdout.write("\n[PTY Wrapper] Prompt detected. Sending 'n'...\n")
                    sys.stdout.flush()
                    os.write(master_fd, b"n\n")
                    
                if "https://" in output and "Enter authorization code:" in output:
                    break
            except OSError:
                break
        time.sleep(0.5)

    sys.stdout.write("\nFinished reading initial process state.\n")
    sys.stdout.flush()

    with open(output_path, "w") as f:
        f.write(output)

    sys.stdout.write(f"Waiting for authorization code at {code_path}...\n")
    sys.stdout.flush()
    
    code_found = False
    for i in range(120): # up to 10 minutes
        if os.path.exists(code_path):
            try:
                with open(code_path, "r") as f:
                    code = f.read().strip()
                if code:
                    sys.stdout.write(f"Sending code to Firebase CLI process...\n")
                    sys.stdout.flush()
                    os.write(master_fd, (code + "\n").encode())
                    os.remove(code_path)
                    code_found = True
                    break
            except Exception as e:
                sys.stdout.write(f"Error reading code file: {e}\n")
                sys.stdout.flush()
        time.sleep(5)

    if not code_found:
        sys.stdout.write("Timeout waiting for code.txt. Terminating.\n")
        sys.stdout.flush()
        proc.terminate()
        return

    time.sleep(5)
    final_output = ""
    for _ in range(10):
        r, w, x = select.select([master_fd], [], [], 0.5)
        if master_fd in r:
            try:
                data = os.read(master_fd, 4096).decode('utf-8', errors='ignore')
                if not data:
                    break
                final_output += data
                sys.stdout.write(data)
                sys.stdout.flush()
            except OSError:
                break
        time.sleep(0.5)

    with open(final_path, "w") as f:
        f.write(final_output)

    proc.wait()
    sys.stdout.write("Process finished.\n")
    sys.stdout.flush()

if __name__ == "__main__":
    run()
