# OAuth

OAuth 服务器部署

## 1. 创建 Secret（如果有必要的话）

```bash
kubectl create namespace ecosystem

kubectl create --namespace ecosystem secret docker-registry    \
  --docker-server=registry.daisukide.com:2083 \
  --docker-username=<用户名> \
  --docker-password=<密码>
```

## PVC

```bash
kubectl apply -f - <<EOF
kind: PersistentVolumeClaim
apiVersion: v1
metadata:
  name: oauth-storage-pvc
  namespace: ecosystem
  labels:
    app: oauth
    framework: laravel
spec:
    accessModes:
      - ReadWriteMany
    resources:
      requests:
        storage: 1Gi
EOF
```

## 执行部署

```bash
kubectl apply -f manifest.yaml
```

## 给 Gitlab Runner 赋予命名空间的操作权限

```bash
kubectl create clusterrolebinding gitlab-runner-access --namespace=ecosystem --serviceaccount=ecosystem:gitlab-runner --clusterrole=edit

kubectl auth can-i create deployments --namespace=ecosystem --as=system:serviceaccount:ecosystem:gitlab-runner
```
