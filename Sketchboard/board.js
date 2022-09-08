// functions

const create = (elem, ns=null) => ns ? document.createElementNS('http://www.w3.org/2000/svg', elem) : document.createElement(elem);
const getStyle = (elem, prop) => Number(getComputedStyle(elem)[prop].replace('px', ''));
const resize = (elem, isTextBox) => {
  const container = create('div');
  container.classList.add('resizeCont');
  isTextBox?container.style.height = 'auto':0;
  let top, left;
  const dragFunc = e => {
    container.style.top = (e.clientY-top)<getStyle(topBar, 'height')
      ?getStyle(topBar, 'height')
      :(e.clientY-top)+'px';
    container.style.left = (e.clientX-left)<getStyle(sideBar, 'width')
      ?getStyle(sideBar, 'width')
      :(e.clientX-left)+'px';
  }
  container.addEventListener('mousedown', e => {
    top = e.clientY - getStyle(container, 'top');
    left = e.clientX - getStyle(container, 'left');
    document.addEventListener('mousemove', dragFunc);
    document.addEventListener('mouseup', e => {
      document.removeEventListener('mousemove', dragFunc);
    }, {once:true});
  })

  const resizeElem = create('div');
  resizeElem.setAttribute('style', `
    border: 1px dashed blue;
    display: flex;
    flex-flow: column nowrap;
    justify-content: space-between;
    position: absolute;
    width: 100%;
    height: 100%;
  `)

  const floors = [
    create('div'),
    create('div'),
    create('div'),
  ];
  floors.forEach(elem => {
    elem.setAttribute('style', `
      display: flex;
      justify-content: space-between;
      width: 100%;
    `)
    resizeElem.append(elem);
  })

  let buttons = [];

  for(let i=0; i<8; i++){
    const resizeBtn = create('div');
    resizeBtn.style.height = resizeBtn.style.width = '5px';
    resizeBtn.style.background = 'blue';
    buttons.push(resizeBtn);

    if(i < 3){
      floors[0].append(resizeBtn)
      if(i===0){
        resizeBtn.style.transform = 'translate(-2.5px, -2.5px)';
        resizeBtn.style.cursor = 'nw-resize';
      }else if(i===2){
        resizeBtn.style.transform = 'translate(2.5px, -2.5px)';
        resizeBtn.style.cursor = 'ne-resize';
      }else{
        resizeBtn.style.transform = 'translate(0, -2.5px)';
        resizeBtn.style.cursor = 'n-resize';
      }
    }else if(i < 5){
      floors[1].append(resizeBtn)
      if(i===3){
        resizeBtn.style.transform = 'translate(-2.5px, 0)';
        resizeBtn.style.cursor = 'w-resize';
      }else{
        resizeBtn.style.transform = 'translate(2.5px, 0)';
        resizeBtn.style.cursor = 'e-resize';
      };
    }else{
      floors[2].append(resizeBtn)
      if(i===5){
        resizeBtn.style.transform = 'translate(-2.5px, 2.5px)';
        resizeBtn.style.cursor = 'sw-resize';
      }else if(i===7){
        resizeBtn.style.transform = 'translate(2.5px, 2.5px)';
        resizeBtn.style.cursor = 'se-resize';
      }else{
        resizeBtn.style.transform = 'translate(0, 2.5px)';
        resizeBtn.style.cursor = 's-resize';
      }
    };
  }
  container.append(resizeElem, elem);
  console.log(container)
}

const resizeFunctions = {

}


// Top Bar

const topBar = create('div');
topBar.classList.add('topBar');
topBar.setAttribute('style', `
  height: 50px;
  background: darkgray;
  border-bottom: 2px solid lightgray;
  display: flex;
  justify-content: right;
  align-items: center;
  padding-right: 10%;
  box-sizing: border-box;
`)

const undoBtn = create('button');
undoBtn.innerHTML = 'Undo';
undoBtn.addEventListener('click', e => board.lastChild.remove());

const importBtn = create('button');
importBtn.innerHTML = 'Import';
const fileBtn = create('input');
fileBtn.type = 'file';
importBtn.addEventListener('click', e => fileBtn.click());
fileBtn.addEventListener('input', e => {
  if(fileBtn.files[0].type === 'image/svg+xml'){
    const reader = new FileReader();
    reader.onload = e => {
      boardParent.innerHTML = e.target.result;
      board = boardParent.children[0]
      board.style.width = board.style.height = '100%';
      addListener(board)
    }
    reader.readAsText(fileBtn.files[0]);
  }else{
    alert('Only svg files are allowed.');
  }
})

const exportBtn = create('button');
exportBtn.innerHTML = 'Export';


const anchor = create('a');
exportBtn.addEventListener('click', () => {
  anchor.setAttribute('href', 'data:text/plain;charset=utf-8, ' + encodeURIComponent(boardParent.innerHTML));
  anchor.setAttribute('download', 'sketch'+parseInt(Math.random()*10000)+'.svg')
  anchor.click();
})

const penWidth = create('input');
penWidth.type = 'number';
penWidth.min = penWidth.value = 1;
penWidth.max = 50;
penWidth.classList.add('pen-width');
penWidth.addEventListener('change', e => e.target.value === '' ? e.target.value = 1 : 0)

const colorMain = create('div');
colorMain.classList.add('color');
colorMain.title = 'Black';

const colorCont = create('div');
colorCont.setAttribute('style', `
  margin-top: 35px;
  margin-left: 50%;
  transform: translate(-50%, 0);
`)
colorCont.classList.add('container');

const colors = ['Brown', 'Red', 'Green', 'Black', 'Blue', 'Yellow', 'Pink', 'Purple', 'Orange', 'Skyblue'];
colors.forEach(elem => {
  const color = create('div');
  color.style.backgroundColor = color.title = elem;
  color.style.margin = '2.5px';
  color.addEventListener('click', e => {
    colorMain.style.backgroundColor = colorMain.title = elem;
    colorCont.style.display = 'none';
  })
  colorCont.append(color);
})

colorCont.addEventListener('focusout', e => colorCont.style.display = 'none');
colorMain.addEventListener('click', e => !colorCont.contains(e.target)?colorCont.style.display = 'flex':0);

colorMain.append(colorCont)

topBar.append(colorMain, penWidth, undoBtn, importBtn, exportBtn);
document.body.append(topBar);


// Side Bar

const sideBar = create('div');
sideBar.classList.add('sideBar');
sideBar.setAttribute('style', `
  width: 50px;
  height: ${window.innerHeight-50}px;
  background: lightgray;
  display: inline-block;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-flow: column wrap;
`)

const textBtn = create('div');
textBtn.title = 'Add textbox.';
textBtn.style.backgroundImage = 'url("Pictures/Text-cursor.png")';
textBtn.addEventListener('click', e => {
  const textbox = create('textarea');
  textbox.setAttribute('style', `
    width: 100%;
    height: 100%;
    background: transparent;
    outline: none;
    border: none;
    resize: none;
  `)
  textbox.addEventListener('scroll', e => {
    textbox.style.height = textbox.scrollHeight+'px';
  })
  resize(textbox, true);
  textbox.parentElement.addEventListener('focusout', e => {
    // e.target.remove();
  })
  boardParent.prepend(textbox.parentElement);
  textbox.focus();
})

const shapeBtn = create('div');
shapeBtn.title = 'Shapes';
shapeBtn.style.backgroundImage = 'url("Pictures/Shapes.png")';

const imageBtn = create('div');
imageBtn.title = 'Insert an Image.';
imageBtn.style.backgroundImage = 'url("Pictures/Image-icon.webp")';

const laserBtn = create('div');
laserBtn.title = 'Laser';
laserBtn.style.backgroundImage = 'url("Pictures/Laser.png")';
laserBtn.style.backgroundSize = '35px 15px';

sideBar.append(textBtn, shapeBtn, imageBtn, laserBtn);


// Board

const boardParent = create('div');
boardParent.style.height = `${window.innerHeight - 52}px`;
boardParent.style.width = `${window.innerWidth - 50}px`;

let board = create('svg', true);
board.classList.add('board');
let pointCount;

const drawFunc = e => {
  const prevPoints = board.lastChild.getAttribute('points')?board.lastChild.getAttribute('points'):'';
  const currPoint = `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `;
  board.lastChild.setAttribute('points', (pointCount===1?currPoint:'')+prevPoints+currPoint);
  pointCount++;
}

const addListener = elem => {
  elem.addEventListener('mousedown', e => {
    const line = create('polyline', true);
    line.setAttribute('style', `
      fill: transparent;
      stroke-linejoin: round;
      stroke: ${getComputedStyle(colorMain).backgroundColor};
      stroke-width: ${penWidth.value+'px'};
    `)
    pointCount = 0;
    board.addEventListener('mousemove', drawFunc)
    board.addEventListener('mouseup', upFunc, {once:true})
    board.append(line);
  })
}

const upFunc = e => {
  board.removeEventListener('mousemove', drawFunc);
  const points = board.lastChild?.getAttribute('points')?.trim();
  const prevPoint = points?.slice(points.slice(0, points.lastIndexOf(' ')).lastIndexOf(' '), points.lastIndexOf(' '));
  points?board.lastChild?.setAttribute('points', points+prevPoint):board.lastChild.remove();
}
addListener(board);

boardParent.append(board);
const paarent = create('div');
paarent.style.display = 'flex';


paarent.append(sideBar, boardParent);
document.body.append(paarent);