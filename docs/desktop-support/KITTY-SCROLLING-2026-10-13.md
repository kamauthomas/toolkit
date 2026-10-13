# Kitty mouse scrolling — 13 October 2026

The affected left pane runs Codex CLI 0.162.1 in Kitty 0.48.2. Kitty's loaded settings retain 10,000 scrollback lines and a wheel multiplier of 3.0, with no custom wheel interception found. Remote window inspection confirmed the Codex pane was in the alternate screen; the adjacent shell pane was in the main screen.

Applied the documented Codex setting `tui.alternate_screen = "never"` in `/home/t316/.codex/config.toml`. Only that setting and its explanatory comment were added. A mode-600 backup is retained at `/home/t316/.codex/config.toml.backup-kitty-scroll-20261013-150456`. All other parsed configuration values were preserved.

**Verification:** TOML parsing and exact before/after value comparison passed. Installed Codex loaded the configuration successfully. An isolated, six-second TUI startup with strict configuration validation produced 1,316 output bytes, no startup error and no alternate-screen entry sequence. No prompt was sent; the isolated child was stopped. The running pane still reports alternate-screen mode, so the fix takes effect after restarting Codex.

**Verification limits:** Actual mouse movement in a restarted session has not been tested. The first validation attempt used `--strict-config` with `codex features`, which returned `Error: --strict-config is not supported for codex features`. The supported configuration load and isolated TUI checks above passed. Sandboxed remote inspection could not connect to Kitty's socket; approved host inspection succeeded.

**Next action:** Exit Codex normally, start `codex resume` from the project directory and select the conversation to continue. Use the wheel over that pane to confirm scrolling. The active session was left running.

**Rollback:** Restore the retained backup if no subsequent configuration edits have been made, or remove only the new `alternate_screen = "never"` line and its comment. Restart Codex afterward.

Sources: [OpenAI advanced TUI configuration](https://learn.chatgpt.com/docs/config-file/config-advanced#tui-options) and [Kitty scrollback configuration](https://sw.kovidgoyal.net/kitty/conf/#scrollback).
