# Lourdes Agent System Folder

This directory is the system configuration folder for the AI agent **Lourdes**. It houses custom skills, session memory, and registry configuration for orchestrating agent behaviors.

## Folder Structure
- **`skills/`**: Contains markdown (`.md`) files representing system prompts and execution logic for individual agent skills.
- **`config/`**: Contains `skills.json` which serves as the registry mapping skills, descriptions, and trigger phrases.
- **`memory/`**: Contains `context.json` to keep track of persistent session variables and history.

## How to Add a New Skill
1. **Write the Prompt**: Define the target role and system instructions for the skill.
2. **Save the File**: Save it as a markdown file inside `skills/` (e.g., `skills/new_skill.md`).
3. **Register the Skill**: Add a corresponding entry for it inside `config/skills.json`.

## Running a Skill in Antigravity
To execute a skill, read and copy the contents of the skill's `.md` file, then paste them directly into the system prompt input of your Antigravity agent configuration.
