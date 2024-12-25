import axios from "axios";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";

const postProjectSchema = z.object({
	projectName: z.string().min(1, "プロジェクト名は必須です"),
	postCode: z.string().min(1, "郵便番号は必須です"),
	address: z.string().min(1, "住所は必須です"),
	content: z.string().min(1, "施工内容は必須です"),
	point: z.string().min(1, "重視したいポイントは必須です"),
	file: z.custom<FileList>(),
});

type PostProjectSchema = z.infer<typeof postProjectSchema>;

const FileUploadForm = () => {
	const { register, handleSubmit, reset } = useForm<PostProjectSchema>();
	const [url, setUrl] = useState("");
	const [error, setError] = useState<string | null>(null);

	const onSubmit = async (data: PostProjectSchema) => {
		setError(null); // エラーメッセージをリセット

		try {
			// ステップ 1: ファイルのアップロード
			const formData = new FormData();
			formData.append("file", data.file[0]);

			const uploadResponse = await axios.post(
				"http://hikaru-sugita-wordpress.local/wp-json/custom/v1/upload",
				formData,
				{
					withCredentials: true,
					headers: {
						"Content-Type": "multipart/form-data",
					},
				},
			);

			if (uploadResponse.status !== 200) {
				throw new Error("ファイルのアップロードに失敗しました");
			}

			const fileUrl = uploadResponse.data.file_url;
			setUrl(fileUrl); // ファイルURLをステートに保存

			// ステップ 2: プロジェクトの登録
			const projectData = {
				projectName: data.projectName,
				postCode: data.postCode,
				address: data.address,
				content: data.content,
				point: data.point,
				fileUrl: fileUrl, // アップロードしたファイルのURLを含める
			};

			const projectResponse = await axios.post(
				"http://hikaru-sugita-wordpress.local/wp-json/custom/v1/project",
				projectData,
				{
					headers: {
						"Content-Type": "application/json",
					},
				},
			);

			if (projectResponse.status === 200) {
				console.log("プロジェクト登録成功:", projectResponse.data);
				reset(); // フォームをリセット
				setUrl(""); // URLをリセット
			} else {
				throw new Error("プロジェクトの登録に失敗しました");
			}
		} catch (err) {
			console.error("エラー:", err);
		}
	};

	const fetchAddress = async () => {
		const postalCode = "9301459"; // フォームから取得する場合は適宜変更
		try {
			const result = await fetch(
				`https://jp-postal-code-api.ttskch.com/api/v1/${postalCode}.json`,
			);
			const { addresses } = await result.json();

			if (addresses) {
				const prefecture = addresses[0].ja.prefecture;
				console.log(prefecture);
			}
		} catch (err) {
			console.error("住所取得エラー:", err);
		}
	};

	useEffect(() => {
		const init = async () => {
			const response = await axios.get(
				"http://hikaru-sugita-wordpress.local/wp-json/wp/v2/users/me",
				{
					withCredentials: true, // 認証用Cookieを送信
				},
			);

			console.log("ユーザー情報:", response.data);
		};
		init();
	}, []);

	return (
		<div>
			<form onSubmit={handleSubmit(onSubmit)}>
				<div>
					<p>プロジェクト名:</p>
					<input type="text" {...register("projectName")} />
				</div>
				<div>
					<p>郵便番号:</p>
					<input type="text" {...register("postCode")} />
					<button type="button" onClick={fetchAddress}>
						get
					</button>
				</div>
				<div>
					<p>住所:</p>
					<input type="text" {...register("address")} />
				</div>
				<div>
					<p>施工内容:</p>
					<input type="text" {...register("content")} />
				</div>
				<div>
					<p>重視したいポイント:</p>
					<input type="text" {...register("point")} />
				</div>
				<div>
					<p>ファイルを選択:</p>
					<input type="file" {...register("file")} />
				</div>

				<button type="submit">アップロード</button>
			</form>
			{url && (
				<p>
					アップロードされたファイルURL: <a href={url}>{url}</a>
				</p>
			)}
			{error && <p style={{ color: "red" }}>エラー: {error}</p>}
		</div>
	);
};

export default FileUploadForm;
