# Vuexy Admin Template - Multi-stage Docker Build
FROM node:22-alpine AS builder

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    git \
    python3 \
    make \
    g++ \
    && rm -rf /var/cache/apk/*

# Copy package files
COPY full-version/package*.json ./
COPY full-version/yarn.lock ./

# Install dependencies
RUN npm ci --omit=dev --legacy-peer-deps && npm cache clean --force

# Copy source code
COPY full-version/ ./

# Build the application
RUN npm run build:prod

# Production stage
FROM nginx:alpine AS production

# Install additional tools for clustering
RUN apk add --no-cache \
    curl \
    bash \
    && rm -rf /var/cache/apk/*

# Copy built assets from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html
COPY --from=builder /app/html /usr/share/nginx/html/templates

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Create health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expose port
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]