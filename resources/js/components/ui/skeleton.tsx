import { cn } from "@/lib/utils"

function Skeleton({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="skeleton"
      className={cn("bg-primary/10 animate-pulse rounded-md", className)}
      {...props}
    />
  )
}

interface MenuSkeletonProps {
  className?: string;
  itemCount?: number;
}

function MenuSkeleton({ className, itemCount = 3 }: MenuSkeletonProps) {
  return (
    <nav className={cn('space-y-1', className)}>
      {Array.from({ length: itemCount }).map((_, index) => (
        <div key={index} className="flex items-center space-x-3 px-3 py-2 rounded-md">
          <Skeleton className="h-4 w-4 shrink-0" />
          <Skeleton className={cn(
            'h-4',
            // Varia o tamanho para parecer mais natural
            index % 3 === 0 ? 'w-24' : index % 3 === 1 ? 'w-32' : 'w-28'
          )} />
        </div>
      ))}
    </nav>
  );
}

export { Skeleton, MenuSkeleton }
