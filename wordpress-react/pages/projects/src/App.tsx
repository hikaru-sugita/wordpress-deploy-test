import { useEffect, useState } from "react";
import Header from "./components/Header";

function App() {
	const [users, setUsers] = useState<{ id: number; name: string }[]>([]);

	useEffect(() => {
		const init = async () => {
			const result = await fetch("https://jsonplaceholder.typicode.com/users");
			const users = await result.json();
			setUsers(users);
		};
		init();
	}, []);

	return (
		<>
			<Header />
			<ul>
				{users.map((user) => (
					<li key={user.id}>
						{user.id}/{user.name}
					</li>
				))}
			</ul>
		</>
	);
}

export default App;
