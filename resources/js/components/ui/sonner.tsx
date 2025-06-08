import { useTheme } from "next-themes"
import { Toaster as Sonner, ToasterProps } from "sonner"

const Toaster = ({ ...props }: ToasterProps) => {
  const { theme = "system" } = useTheme()

  return (
    <Sonner
      theme={theme as ToasterProps["theme"]}
      className="toaster group"
      toastOptions={{
        style: {
          background: "hsl(var(--background))",
          border: "1px solid hsl(var(--border))",
          color: "hsl(var(--foreground))",
        },
        classNames: {
          success: "toast-success",
          error: "toast-error", 
          warning: "toast-warning",
          info: "toast-info",
          loading: "toast-loading",
        },
      }}
      style={
        {
          "--normal-bg": "hsl(var(--background))",
          "--normal-text": "hsl(var(--foreground))",
          "--normal-border": "hsl(var(--border))",
          "--success-bg": "hsl(142.1 76.2% 36.3%)",
          "--success-text": "hsl(355.7 100% 97.3%)",
          "--success-border": "hsl(142.1 76.2% 36.3%)",
          "--error-bg": "hsl(0 84.2% 60.2%)",
          "--error-text": "hsl(0 0% 98%)",
          "--error-border": "hsl(0 84.2% 60.2%)",
          "--warning-bg": "hsl(47.9 95.8% 53.1%)",
          "--warning-text": "hsl(26 83.3% 14.1%)",
          "--warning-border": "hsl(47.9 95.8% 53.1%)",
          "--info-bg": "hsl(221.2 83.2% 53.3%)",
          "--info-text": "hsl(0 0% 98%)",
          "--info-border": "hsl(221.2 83.2% 53.3%)",
          "--loading-bg": "hsl(262.1 83.3% 57.8%)",
          "--loading-text": "hsl(0 0% 98%)",
          "--loading-border": "hsl(262.1 83.3% 57.8%)",
        } as React.CSSProperties
      }
      position="bottom-right"
      duration={4000}
      {...props}
    />
  )
}

export { Toaster }
