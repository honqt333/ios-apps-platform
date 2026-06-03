export default function SettingsPage() {
  return (
    <div className="space-y-4">
      <h1 className="text-3xl font-bold">Settings</h1>
      <p className="text-muted-foreground">
        Platform-level settings are configured via the backend <code>.env</code> file.
      </p>
    </div>
  );
}
