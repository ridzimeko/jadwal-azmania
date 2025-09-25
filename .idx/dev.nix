{pkgs}: {
  channel = "stable-25.05";
  packages = [
    pkgs.nodejs_22
    pkgs.php83
    pkgs.php83Packages.composer
    pkgs.php83Extensions.intl
    pkgs.php83Extensions.zip
  ];
  idx.extensions = [
    "esbenp.prettier-vscode"
    "dbaeumer.vscode-eslint"
    "bradlc.vscode-tailwindcss"
    "DavidAnson.vscode-markdownlint"
    "EditorConfig.EditorConfig"
    "mikestead.dotenv"
    "usernamehw.errorlens"
    "bmewburn.vscode-intelephense-client"
    "raunofreiberg.vesper"
    "robsontenorio.voltage"
    "shufo.vscode-blade-formatter"
    "vscode-icons-team.vscode-icons"
  ];
  idx.previews = {
    # previews = {
    #   web = {
    #     command = [
    #       "npm"
    #       "run"
    #       "dev"
    #       "--"
    #       "--port"
    #       "$PORT"
    #       "--host"
    #       "0.0.0.0"
    #     ];
    #     manager = "web";
    #   };
    # };
  };
}
