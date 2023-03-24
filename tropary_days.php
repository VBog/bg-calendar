<?php
function tropary_days ($wd, $tone) {
	$tropary_days = [	
		'<p><em>В понедельник, Небесных чинов Бесплотных</em></p>
		<h4>Тропарь, глас 4:</h4>
		<p>Небесных воинств Архистратизи,/ молим вас присно мы недостойнии,/ да вашими молитвами оградите нас/ кровом крил невещественныя вашея славы,/ сохраняюще нас, припадающих прилежно и вопиющих:/ от бед избавите нас,// яко чиноначальницы вышних Сил.</p>
		<h4>Кондак, глас 2:</h4>
		<p>Архистрати́зи Бо́жии,/ служи́телие Боже́ственныя сла́вы,/ А́нгелов нача́льницы, и челове́ков наста́вницы,/ поле́зное нам проси́те, и ве́лию ми́лость,// я́ко Безпло́тных Архистрати́зи.</p>',

		'<p><em>Во вторник, Крестителя и Предтечи Иоанна</em></p>
		<h4>Тропарь, глас 2:</h4>
		<p>Память праведнаго с похвалами,/ тебе же довлеет свидетельство Господне, Предтече:/ показал бо ся еси воистину и пророков честнейший,/ яко и в струях крестити сподобился еси Проповеданнаго./ Темже за истину пострадав радуяся,/ благовестил еси и сущим во аде Бога, явльшагося плотию,/ вземлющаго грех мира// и подающаго нам велию милость.</p>
		<h4>Кондак, глас 2:</h4>
		<p>Про́роче Бо́жий и Предте́че благода́ти,/ главу́ твою́ я́ко ши́пок свяще́ннейший от земли́ обре́тше,/ исцеле́ния всегда́ прие́млем,// и́бо па́ки, я́коже пре́жде в ми́ре пропове́дуеши покая́ние.</p>',

		'<p><em>В среду и пятницу, Креста</em></p>
		<h4>Тропарь, глас 1:</h4> 
		<p>Спаси, Господи, люди Твоя/ и благослови достояние Твое,/ победы на сопротивныя даруя// и Твое сохраняя Крестом Твоим жительство.</p>
		<h4>Кондак, глас 4:</h4>
		<p>Вознесы́йся на Крест во́лею,/ тезоимени́тому Твоему́ но́вому жи́тельству,/ щедро́ты Твоя́ да́руй, Христе́ Бо́же,/ возвесели́ нас си́лою Твое́ю,/ побе́ды дая́ нам на сопоста́ты,/ посо́бие иму́щим Твое́ ору́жие ми́ра,// непобеди́мую побе́ду.</p>',
		
		'<p><em>В четверг, апостолов и святителя Николая</em></p>
		<h4>Тропарь апостолов, глас 3:</h4>
		<p>Апостоли святии,/ молите Милостиваго Бога,/ да прегрешений оставление// подаст душам нашим.</p>
		<h4>Кондак апостолам, глас 2:</h4>
		<p>Тве́рдыя и боговеща́нныя пропове́датели,/ верх апо́столов Твои́х, Го́споди,/ прия́л еси́ в наслажде́ние благи́х Твои́х и поко́й;/ боле́зни бо о́нех и смерть прия́л еси́ па́че вся́каго всепло́дия,// Еди́не све́дый серде́чная.</p>
		<h4>Тропарь святителя, глас 4:</h4>
		<p>Правило веры и образ кротости,/ воздержания учителя/ яви тя стаду твоему/ Яже вещей Истина./ Сего ради стяжал еси смирением высокая,/ нищетою богатая,/ отче священноначальниче Николае,/ моли Христа Бога,// спастися душам нашим.</p>
		<h4>Кондак святителю Николаю, глас 3:</h4>
		<p>В Ми́рех, свя́те, священноде́йствитель показа́лся еси́,/ Христо́во бо, преподо́бне, Ева́нгелие испо́лнив,/ положи́л еси́ ду́шу твою́ о лю́дех твои́х/ и спасл еси́ непови́нныя от сме́рти./ Сего́ ра́ди освяти́лся еси́,// я́ко вели́кий таи́нник Бо́жия благода́ти.</p>',
		
		'<p><em>В среду и пятницу, Креста</em></p>
		<h4>Тропарь, глас 1:</h4> 
		<p>Спаси, Господи, люди Твоя/ и благослови достояние Твое,/ победы на сопротивныя даруя// и Твое сохраняя Крестом Твоим жительство.</p>
		<h4>Кондак, глас 4:</h4>
		<p>Вознесы́йся на Крест во́лею,/ тезоимени́тому Твоему́ но́вому жи́тельству,/ щедро́ты Твоя́ да́руй, Христе́ Бо́же,/ возвесели́ нас си́лою Твое́ю,/ побе́ды дая́ нам на сопоста́ты,/ посо́бие иму́щим Твое́ ору́жие ми́ра,// непобеди́мую побе́ду.</p>',
				
		'<p><em>В субботу, всех святых, и за умерших</em></p>
		<h4>Тропарь всем святым, глас 2:</h4>
		<p>Апостоли, мученицы и пророцы,/ святителие, преподобнии и праведнии,/ добре подвиг совершившии и веру соблюдшии,/ дерзновение имущии ко Спасу,/ о нас Того, яко Блага, молите// спастися, молимся, душам нашим.</p> 
		<h4>Кондак всем святым, глас 8:</h4>
		<p>Я́ко нача́тки естества́, Насади́телю тва́ри,/ вселе́нная прино́сит Ти, Го́споди, богоно́сныя му́ченики;/ тех моли́твами в ми́ре глубо́це// Це́рковь Твою́, жи́тельство Твое́ Богоро́дицею соблюди́, Многоми́лостиве.</p>		
		<h4>Тропарь за умерших, глас 2:</h4>
		<p>Помяни, Господи, яко Благ, рабы Твоя,/ и елика в житии согрешиша, прости:/ никтоже бо безгрешен,// токмо Ты, могий и преставленным дати покой.</p>
		<h4>Кондак за умерших, глас 8:</h4>
		<p>Со святы́ми упоко́й,/ Христе́,/ ду́ши раб Твои́х,/ иде́же несть боле́знь, ни печа́ль,/ ни воздыха́ние,// но жизнь безконе́чная.</p>'
	];
	$tropary_sunday = [
		'<h4>Тропарь воскресный, глас 1</h4>
		<p>Камени запечатану от иудей/ и воином стрегущим Пречистое Тело Твое,/ воскресл еси тридневный, Спасе,/ даруяй мирови жизнь./ Сего ради Силы Небесныя вопияху Ти, Жизнодавче:/ слава Воскресению Твоему, Христе,/ слава Царствию Твоему,// слава смотрению Твоему, едине Человеколюбче.</p>
		<h4>Кондак воскресный, глас 1</h4>
		<p>Воскре́сл еси́ я́ко Бо́г из гро́ба во сла́ве, / и ми́р совоскреси́л еси́; / и естество́ челове́ческое я́ко Бо́га воспева́ет Тя́, и сме́рть исчезе́; / Ада́м же лику́ет, Влады́ко; / Е́ва ны́не от у́з избавля́ема ра́дуется, зову́щи: / Ты́ еси́, И́же все́м подая́, Христе́, воскресе́ние.</p>',

		'<h4>Тропарь воскресный, глас 2</h4>
		<p>Егда снизшел еси к смерти, Животе Безсмертный,/ тогда ад умертвил еси блистанием Божества;/ егда же и умершия от преисподних воскресил еси,/ вся Силы Небесныя взываху:// Жизнодавче, Христе Боже наш, слава Тебе.</p>
		<h4>Кондак воскресный, глас 2</h4>
		<p>Воскре́сл еси́ от гро́ба, Всеси́льне Спа́се, / и а́д ви́дев чу́до, ужасе́ся, / и ме́ртвии воста́ша; / тва́рь же ви́дящи сра́дуется Тебе́, / и Ада́м свесели́тся, / и ми́р, Спа́се мо́й, воспева́ет Тя́ при́сно.</p>',

		'<h4>Тропарь воскресный, глас 3</h4>
		<p>Да веселятся Небесная,/ да радуются земная,/ яко сотвори державу/ мышцею Своею Господь,/ попра смертию смерть,/ первенец мертвых бысть;/ из чрева адова избави нас,// и подаде мирови велию милость.</p>
		<h4>Кондак воскресный, глас 3</h4>
		<p>Воскре́сл еси́ дне́сь из гро́ба, Ще́дре, / и на́с возве́л еси́ от вра́т сме́ртных; / дне́сь Ада́м лику́ет, и ра́дуется Е́ва, / вку́пе же и проро́цы с патриа́рхи воспева́ют непреста́нно / Боже́ственную держа́ву вла́сти Твоея́.</p>',

		'<h4>Тропарь воскресный, глас 4</h4>
		<p>Светлую Воскресения проповедь/ от Ангела уведевша Господни ученицы/ и прадеднее осуждение отвергша,/ апостолом хвалящася глаголаху:/ испровержеся смерть,/ воскресе Христос Бог,// даруяй мирови велию милость.</p>
		<h4>Кондак воскресный, глас 4</h4>
		<p>Спа́с и Изба́витель мо́й / из гро́ба, я́ко Бо́г, / воскреси́ от у́з земноро́дныя, / и врата́ а́дова сокруши́, / и я́ко Влады́ка воскре́се тридне́вен.</p>',

		'<h4>Тропарь воскресный, глас 5</h4>
		<p>Собезначальное Слово Отцу и Духови,/ от Девы рождшееся на спасение наше,/ воспоим, вернии, и поклонимся,/ яко благоволи плотию взыти на Крест,/ и смерть претерпети,/ и воскресити умершия// славным Воскресением Своим.</p>
		<h4>Кондак воскресный, глас 5</h4>
		<p>Ко а́ду, Спа́се мо́й, соше́л еси́, / и врата́ сокруши́вый я́ко Всеси́лен, / уме́рших я́ко Созда́тель совоскреси́л еси́, / и сме́рти жа́ло сокруши́л еси́, / и Ада́м от кля́твы изба́влен бы́сть, / Человеколю́бче, те́мже вси́ зове́м: / спаси́ на́с, Го́споди.</p>',

		'<h4>Тропарь воскресный, глас 6</h4>
		<p>Ангельския Силы на гробе Твоем,/ и стрегущии омертвеша,/ и стояше Мария во гробе,/ ищущи Пречистаго Тела Твоего./ Пленил еси ад, не искусився от него;/ сретил еси деву,/ даруяй живот.// Воскресый из мертвых, Господи, слава Тебе.</p>
		<h4>Кондак воскресный, глас 6</h4>
		<p>Живонача́льною дла́нию / уме́ршия от мра́чных удо́лий, / Жизнода́вец, воскреси́в все́х Христо́с Бо́г, / воскресе́ние подаде́ челове́ческому ро́ду: / е́сть бо все́х Спаси́тель, / воскресе́ние и живо́т, и Бо́г все́х.</p>',

		'<h4>Тропарь воскресный, глас 7</h4>
		<p>Разрушил еси Крестом Твоим смерть,/ отверзл еси разбойнику рай,/ мироносицам плач преложил еси,/ и апостолом проповедати повелел еси,/ яко воскресл еси, Христе Боже,/ даруяй мирови// велию милость.</p>
		<h4>Кондак воскресный, глас 7</h4>
		<p>Не ктому́ держа́ва сме́ртная / возмо́жет держа́ти челове́ки: / Христо́с бо сни́де, сокруша́я и разоря́я си́лы ея́; / связу́емь быва́ет а́д, / проро́цы согла́сно ра́дуются, / предста́, глаго́люще, Спа́с су́щим в ве́ре: / изыди́те, ве́рнии, в воскресе́ние.</p>',

		'<h4>Тропарь воскресный, глас 8</h4>
		<p>С высоты снизшел еси, Благоутробне,/ погребение приял еси тридневное,/ да нас свободиши страстей,// Животе и Воскресение наше, Господи, слава Тебе.</p>
		<h4>Кондак воскресный, глас 8</h4>
		<p>Воскре́с из гро́ба, уме́ршия воздви́гл еси́, / и Ада́ма воскреси́л еси́, / и Е́ва лику́ет во Твое́м воскресе́нии, / и мирсти́и концы́ торжеству́ют / е́же из ме́ртвых воста́нием Твои́м, Многоми́лостиве.</p>'
	];
	if ($wd < 1 || $wd > 7 || $tone < 1 || $tone > 8) return '';
	
	if ($wd < 7) {
		$tropary = $tropary_days[$wd-1];
	} else {
		$tropary = $tropary_sunday[$tone-1];
	}
	
	return $tropary;
}